<?php
// File: app/Http/Controllers/Web/Announcer/SettingsController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        
        // Get user profile
        $viewData["user"] = $this->userService->getUserById($userId);
        
        $this->setViewData($request, $viewData);
        
        return view('annonceur.settings.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Paramètres',
            'pagetilte' => 'Paramètres',
            'pagecardtilte' => 'Gérer votre compte et vos préférences'
        ]);
    }
    
    public function update(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        $userData = [
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'country_id' => $request->input('country_id'),
            'locality_id' => $request->input('locality_id'),
            'phone' => $request->input('phone')
        ];
        
        if (!empty($request->input('password'))) {
            $userData['password'] = $request->input('password');
        }
        
        $result = $this->userService->updateUser($userId, $userData);
        
        if ($result['success']) {
            return redirect()->route('annonceur.settings.index')
                ->with('type', 'success')
                ->with('message', 'Profil mis à jour avec succès');
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