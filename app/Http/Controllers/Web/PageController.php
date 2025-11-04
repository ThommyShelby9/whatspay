<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Traits\Utils;
use Illuminate\Http\Request;

class PageController extends Controller
{
    use Utils;
    
    public function home(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        $countries = Country::all();

        $this->setViewData($request, $viewData);
        
        return view('home', [
            'baseurl' => config('app.url'),
            'version' => gmdate("YmdHis"),
            'countries' => $countries, 
            'alert' => $alert, 
            'viewData' => $viewData,
            'title' => 'WhatsPAY',
        ]);
    }
    
    public function page(Request $request, $page)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        $countries = Country::all();
        $titlepage = "";

        switch ($page) {
            case 'contact':
                $titlepage = "CONTACTEZ-NOUS";
                break;
            case 'support':
                $titlepage = "SUPPORT";
                break;
            case 'faq':
                $titlepage = "FOIRE AUX QUESTIONS";
                break;
            case 'politique':
                $titlepage = "POLITIQUE DE CONFIDENTIALITÉ";
                break;
            case 'conditions':
                $titlepage = "CONDITIONS D'UTILISATION";
                break;
            case 'mentions':
                $titlepage = "MENTIONS LÉGALES";
                break;
        }

        $this->setViewData($request, $viewData);
        
        return view('page', [
            'baseurl' => config('app.url'),
            'version' => gmdate("YmdHis"),
            'countries' => $countries, 
            'alert' => $alert, 
            'viewData' => $viewData,
            'title' => 'WhatsPAY > ' . strtoupper($page),
            'titlepage' => $titlepage,
            'page' => $page
        ]);
    }
    
    public function comingsoon(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $countries = Country::all();

        $this->setViewData($request, $viewData);
        
        return view('comingsoon', [
            'baseurl' => config('app.url'),
            'version' => gmdate("YmdHis"),
            'countries' => $countries, 
            'alert' => $alert, 
            'viewData' => $viewData,
            'title' => 'WhatsPAY > Coming Soon'
        ]);
    }
    
    private function isConnected()
    {
        return (\Auth::viaRemember() || \Auth::check());
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