<?php
// File: app/Http/Controllers/Web/Announcer/ReportController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use App\Services\TrackingService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use Utils;
    
    protected $taskService;
    protected $trackingService;
    
    public function __construct(
        TaskService $taskService,
        TrackingService $trackingService
    ) {
        $this->taskService = $taskService;
        $this->trackingService = $trackingService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        // Get filters
        $filters = [
            'start_date' => $request->get('filtre_start_date'),
            'end_date' => $request->get('filtre_end_date'),
            'client_id' => $userId
        ];
        
        // Get global stats
        $viewData["globalStats"] = $this->trackingService->getGlobalStatistics($filters);
        
        $this->setViewData($request, $viewData);
        
        return view('annonceur.reports.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Rapports',
            'pagetilte' => 'Rapports & Analyses',
            'pagecardtilte' => 'Performances de vos campagnes'
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