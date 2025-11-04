<?php
// File: app/Http/Controllers/Web/Influencer/WhatsAppController.php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    use Utils;
    
    protected $whatsAppService;
    
    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        // TODO: Récupérer les numéros WhatsApp de l'utilisateur
        // $viewData["whatsappNumbers"] = $this->whatsAppService->getUserNumbers($userId);
        
        $this->setViewData($request, $viewData);
        
        return view('influencer.whatsapp.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | WhatsApp',
            'pagetilte' => 'Configuration WhatsApp',
            'pagecardtilte' => 'Gestion des numéros WhatsApp'
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