<?php
// File: app/Http/Controllers/Web/Announcer/WalletController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Services\PaymentService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use Utils;
    
    protected $walletService;
    protected $paymentService;
    
    public function __construct(
        WalletService $walletService,
        PaymentService $paymentService
    ) {
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        // Récupérer le solde du portefeuille
        $viewData['balance'] = $this->walletService->getBalance($userId);
        
        // Récupérer le plan actuel
        $viewData['currentPlan'] = $this->walletService->getCurrentPlan($userId);
        
        // Récupérer les transactions récentes
        $viewData['transactions'] = $this->walletService->getTransactions($userId);
        
        // Récupérer les méthodes de paiement disponibles
        $viewData['paymentMethods'] = $this->paymentService->getAvailablePaymentMethods();
        
        $this->setViewData($request, $viewData);
        
        return view('annonceur.wallet.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Portefeuille',
            'pagetilte' => 'Portefeuille',
            'pagecardtilte' => 'Gérer votre budget et vos paiements'
        ]);
    }
    
    public function addFunds(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        // Valider les données du formulaire
        $request->validate([
            'payment_method' => 'required|string',
            'amount' => 'required|numeric|min:5000',
        ]);
        
        // Traiter la demande d'ajout de fonds
        $result = $this->paymentService->initiatePayment(
            $userId,
            $request->input('payment_method'),
            $request->input('amount')
        );
        
        if ($result['success']) {
            // Si une URL de redirection est fournie pour le paiement
            if (!empty($result['redirect_url'])) {
                return redirect()->away($result['redirect_url']);
            }
            
            return redirect()->route('announcer.wallet')
                ->with('type', 'success')
                ->with('message', $result['message']);
        } else {
            return redirect()->route('announcer.wallet')
                ->with('type', 'danger')
                ->with('message', $result['message']);
        }
    }
    
    private function setAlert(Request &$request, &$alert)
    {
        $alert = [
            'message' => (!empty($request->message) ? $request->message : (!empty(session('message')) ? session('message') : "")),
            'type' => (!empty($request->type) ? $request->type : (!empty(session('type')) ? session('type') : "success")),
        ];
    }

    private function setViewData(Request &$request, &$viewData)
    {
        $viewData['uri'] = \Route::currentRouteName();
        $viewData['baseUrl'] = config('app.url');
        $viewData['version'] = gmdate('YmdHis');
        $viewData['user'] = ($request->session()->has('user') ? $request->session()->get('user') : "");
        $viewData['userid'] = ($request->session()->has('userid') ? $request->session()->get('userid') : "");
        $viewData['userprofile'] = ($request->session()->has('userprofile') ? $request->session()->get('userprofile') : "");
        $viewData['userrights'] = ($request->session()->has('userrights') ? (json_decode($request->session()->get('userrights'), true)) : []);
        $viewData['userfirstname'] = ($request->session()->has('userfirstname') ? $request->session()->get('userfirstname') : "");
        $viewData['userlastname'] = ($request->session()->has('userlastname') ? $request->session()->get('userlastname') : "");
    }
}