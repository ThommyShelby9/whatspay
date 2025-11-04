<?php
// File: app/Http/Controllers/Web/Influencer/EarningController.php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\AssignmentService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class EarningController extends Controller
{
    use Utils;
    
    protected $assignmentService;
    
    public function __construct(
        AssignmentService $assignmentService
    ) {
        $this->assignmentService = $assignmentService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        // Récupérer les statistiques de gains
        $viewData["earningsStats"] = $this->assignmentService->getAgentEarningsStats($userId);
        
        // Historique des paiements
        // TODO: Implémenter cette méthode dans AssignmentService
        //$viewData["paymentHistory"] = $this->assignmentService->getAgentPaymentHistory($userId);
        
        $this->setViewData($request, $viewData);
        
        return view('influencer.earnings.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Mes Gains',
            'pagetilte' => 'Mes Gains',
            'pagecardtilte' => 'Historique des revenus'
        ]);
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