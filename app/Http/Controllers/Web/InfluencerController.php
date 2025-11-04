<?php
// File: app/Http/Controllers/Web/Announcer/InfluencerController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\CategoryService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class InfluencerController extends Controller
{
    use Utils;
    
    protected $userService;
    protected $categoryService;
    
    public function __construct(
        UserService $userService,
        CategoryService $categoryService
    ) {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        // Get filters
        $filters = [
            'country_id' => $request->get('filtre_country'),
            'locality_id' => $request->get('filtre_locality'),
            'category_id' => $request->get('filtre_category'),
        ];
        
        // Get influencers
        $viewData["influencers"] = $this->userService->getUsersByProfile('DIFFUSEUR', $filters);
        $viewData["categories"] = $this->categoryService->getAllCategories();
        
        $this->setViewData($request, $viewData);
        
        return view('annonceur.influencers.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Diffuseurs',
            'pagetilte' => 'Diffuseurs',
            'pagecardtilte' => 'Trouver des diffuseurs pour vos campagnes'
        ]);
    }
    
    public function show(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        // Get influencer details
        $influencer = $this->userService->getUserById($id);
        if (!$influencer) {
            return redirect()->route('annonceur.influencers.index')
                ->with('type', 'danger')
                ->with('message', 'Diffuseur non trouvé');
        }
        
        $viewData["influencer"] = $influencer;
        
        $this->setViewData($request, $viewData);
        
        return view('annonceur.influencers.show', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Profil Diffuseur',
            'pagetilte' => 'Profil Diffuseur',
            'pagecardtilte' => 'Détails du profil diffuseur'
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