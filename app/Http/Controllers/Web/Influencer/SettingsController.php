<?php
// File: app/Http/Controllers/Web/Influencer/SettingsController.php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    use Utils;
    
    protected $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        // Récupérer les paramètres de l'utilisateur
        $viewData["user"] = $this->userService->getUserById($userId);
        
        $this->setViewData($request, $viewData);
        
        return view('influencer.settings.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Paramètres',
            'pagetilte' => 'Paramètres',
            'pagecardtilte' => 'Configuration du compte'
        ]);
    }
    
    public function update(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        // Valider les données soumises selon le type de paramètre
        $type = $request->input('settings_type', 'general');
        
        switch ($type) {
            case 'security':
                $request->validate([
                    'current_password' => 'required|string',
                    'new_password' => 'required|string|min:8|confirmed',
                ]);
                
                // TODO: Vérifier le mot de passe actuel
                // Mettre à jour le mot de passe
                $userData = [
                    'password' => $request->input('new_password')
                ];
                break;
                
            case 'notifications':
                $userData = [
                    'email_notifications' => $request->has('email_notifications'),
                    'whatsapp_notifications' => $request->has('whatsapp_notifications')
                ];
                break;
                
            default: // general
                $userData = [
                    'language' => $request->input('language', 'fr'),
                    'timezone' => $request->input('timezone', 'UTC+0'),
                    'date_format' => $request->input('date_format', 'DD/MM/YYYY')
                ];
                break;
        }
        
        $result = $this->userService->updateUser($userId, $userData);
        
        if ($result['success']) {
            return redirect()->route('influencer.settings.index')
                ->with('type', 'success')
                ->with('message', 'Paramètres mis à jour avec succès');
        } else {
            return redirect()->back()
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