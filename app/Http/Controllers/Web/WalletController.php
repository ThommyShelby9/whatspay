<?php
// File: app/Http/Controllers/Web/WalletController.php (Version sans plans)

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Services\PaymentService;
use App\Services\CampaignBudgetService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    use Utils;
    
    protected $walletService;
    protected $paymentService;
    protected $campaignBudgetService;
    
    public function __construct(
        WalletService $walletService,
        PaymentService $paymentService,
        CampaignBudgetService $campaignBudgetService
    ) {
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
        $this->campaignBudgetService = $campaignBudgetService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        try {
            // Récupérer le solde du portefeuille
            $viewData['balance'] = $this->walletService->getBalance($userId) ?? 0;
            
            // Récupérer les transactions récentes (15 dernières)
            $viewData['transactions'] = $this->walletService->getTransactions($userId, 15) ?? collect([]);
            
            // Récupérer les méthodes de paiement disponibles
            try {
                $viewData['paymentMethods'] = $this->paymentService->getAvailablePaymentMethods() ?? collect([]);
            } catch (\Exception $e) {
                Log::warning('Could not fetch payment methods: ' . $e->getMessage());
                $viewData['paymentMethods'] = collect([]);
            }
            
            // Récupérer les statistiques de dépenses du client
            try {
                $spendingOverview = $this->campaignBudgetService->getClientSpendingOverview($userId);
                if ($spendingOverview['success']) {
                    $viewData['spendingStats'] = $spendingOverview['overview'];
                } else {
                    $viewData['spendingStats'] = $this->getDefaultSpendingStats();
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch spending stats: ' . $e->getMessage());
                $viewData['spendingStats'] = $this->getDefaultSpendingStats();
            }
            
            // Récupérer les statistiques de transactions
            try {
                $viewData['transactionStats'] = $this->walletService->getTransactionStats($userId);
            } catch (\Exception $e) {
                Log::warning('Could not fetch transaction stats: ' . $e->getMessage());
                $viewData['transactionStats'] = $this->getDefaultTransactionStats();
            }
            
            // Vérifier s'il y a des alertes de solde faible
            $viewData['lowBalanceAlert'] = $this->checkLowBalanceAlert($userId);
            
        } catch (\Exception $e) {
            Log::error('Error in wallet index: ' . $e->getMessage());
            
            // Données par défaut en cas d'erreur
            $viewData['balance'] = 0;
            $viewData['transactions'] = collect([]);
            $viewData['paymentMethods'] = collect([]);
            $viewData['spendingStats'] = $this->getDefaultSpendingStats();
            $viewData['transactionStats'] = $this->getDefaultTransactionStats();
            $viewData['lowBalanceAlert'] = null;
            
            // Ajouter un message d'erreur
            $alert = [
                'type' => 'warning',
                'message' => 'Certaines données n\'ont pas pu être chargées. Veuillez actualiser la page.'
            ];
        }
        
        $this->setViewData($request, $viewData);
        
        return view('announcer.wallet.index', [
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
        try {
            $userId = $request->session()->get('userid');
            
            // Validation des données
            $request->validate([
                'payment_method' => 'required|string',
                'amount' => 'required|numeric|min:1000|max:1000000',
                'phone' => 'required|string|regex:/^[0-9]{8,15}$/',
            ], [
                'amount.min' => 'Le montant minimum est de 1 000 FCFA',
                'amount.max' => 'Le montant maximum est de 1 000 000 FCFA',
                'phone.required' => 'Le numéro de téléphone est requis',
                'phone.regex' => 'Format de numéro de téléphone invalide',
            ]);
            
            // Nettoyer le numéro de téléphone
            $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
            if (!str_starts_with($phone, '225')) {
                $phone = '225' . $phone;
            }
            
            // Initier le dépôt via PayPlus
            $result = $this->paymentService->initiateDeposit(
                $userId,
                $request->input('amount'),
                $phone,
                true
            );
            
            if ($result['success']) {
                session([
                    'pending_deposit' => [
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $request->input('amount'),
                        'phone' => $phone
                    ]
                ]);
                
                return redirect()->away($result['redirect_url']);
            } else {
                return redirect()->route('announcer.wallet')
                    ->with('type', 'danger')
                    ->with('message', $result['message']);
            }
            
        } catch (ValidationException $e) {
            return redirect()->route('announcer.wallet')
                ->withErrors($e->errors())
                ->withInput()
                ->with('type', 'danger')
                ->with('message', 'Veuillez corriger les erreurs dans le formulaire');
                
        } catch (\Exception $e) {
            return redirect()->route('announcer.wallet')
                ->with('type', 'danger')
                ->with('message', 'Une erreur est survenue. Veuillez réessayer.');
        }
    }
    
    /**
     * Traiter le retour après paiement PayPlus
     */
    public function handlePaymentReturn(Request $request)
    {
        $status = $request->get('status');
        $pendingDeposit = session('pending_deposit');
        
        if ($pendingDeposit) {
            session()->forget('pending_deposit');
            
            if ($status === 'success') {
                $transactionStatus = $this->paymentService->checkTransactionStatus($pendingDeposit['transaction_id']);
                
                if ($transactionStatus['success'] && $transactionStatus['status'] === 'completed') {
                    return redirect()->route('announcer.wallet')
                        ->with('type', 'success')
                        ->with('message', 'Votre dépôt de ' . number_format($pendingDeposit['amount'], 0, ',', ' ') . ' FCFA a été effectué avec succès !');
                } else {
                    return redirect()->route('announcer.wallet')
                        ->with('type', 'warning')
                        ->with('message', 'Votre paiement est en cours de traitement. Vous recevrez une confirmation sous peu.');
                }
            } else {
                return redirect()->route('announcer.wallet')
                    ->with('type', 'danger')
                    ->with('message', 'Votre paiement a été annulé ou a échoué.');
            }
        }
        
        return redirect()->route('announcer.wallet');
    }
    
    /**
     * Afficher l'historique complet des transactions
     */
    public function transactionHistory(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $viewData['transactions'] = $this->walletService->getPaginatedTransactions($userId, 25);
        $viewData['transactionStats'] = $this->walletService->getTransactionStats($userId);
        $viewData['balance'] = $this->walletService->getBalance($userId);
        
        $this->setViewData($request, $viewData);
        
        return view('announcer.wallet.history', [
            'alert' => $alert,
            'viewData' => $viewData,
            'title' => 'WhatsPAY | Historique des transactions',
            'pagetilte' => 'Historique des transactions',
            'pagecardtilte' => 'Toutes vos transactions'
        ]);
    }
    
    /**
     * API endpoint pour récupérer les statistiques de portefeuille
     */
    public function getWalletStats(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        try {
            $summary = $this->walletService->getWalletSummary($userId);
            $spendingOverview = $this->campaignBudgetService->getClientSpendingOverview($userId);
            
            return response()->json([
                'success' => true,
                'wallet' => $summary,
                'spending' => $spendingOverview['success'] ? $spendingOverview['overview'] : null
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }
    
    /**
     * Get default spending stats
     */
    private function getDefaultSpendingStats()
    {
        return [
            'total_campaigns' => 0,
            'active_campaigns' => 0,
            'total_budget_reserved' => 0,
            'total_spent' => 0,
            'pending_payments' => 0,
            'current_wallet_balance' => 0,
            'total_available' => 0
        ];
    }
    
    /**
     * Get default transaction stats
     */
    private function getDefaultTransactionStats()
    {
        return [
            'total_transactions' => 0,
            'total_credits' => 0,
            'total_debits' => 0,
            'this_month_credits' => 0,
            'this_month_debits' => 0,
            'last_transaction_date' => null
        ];
    }
    
    /**
     * Vérifier les alertes de solde faible
     */
    private function checkLowBalanceAlert($userId)
    {
        $balance = $this->walletService->getBalance($userId);
        
        try {
            $spendingOverview = $this->campaignBudgetService->getClientSpendingOverview($userId);
            
            if ($spendingOverview['success']) {
                $pendingPayments = $spendingOverview['overview']['pending_payments'];
                
                if ($balance < $pendingPayments) {
                    return [
                        'type' => 'danger',
                        'message' => 'Votre solde est insuffisant pour couvrir les paiements en attente (' . number_format($pendingPayments, 0, ',', ' ') . ' FCFA)',
                        'action_required' => true
                    ];
                }
                
                if ($balance < 10000 && $balance > 0) {
                    return [
                        'type' => 'warning',
                        'message' => 'Votre solde est faible (' . number_format($balance, 0, ',', ' ') . ' FCFA). Pensez à le recharger.',
                        'action_required' => false
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not check balance alerts: ' . $e->getMessage());
        }
        
        return null;
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