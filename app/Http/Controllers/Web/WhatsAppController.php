<?php

namespace App\Http\Controllers\Web;

use App\Consts\Util;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Phone;
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

    public function whatsappnumbersGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $this->setViewData($request, $viewData);

        $countries = Country::all();
        $viewData["countries"] = $countries;
        $viewData["countriesJson"] = json_encode($countries);

        $viewData["bjId"] = '';
        foreach ($viewData["countries"] as $country) {
            if (strtoupper($country->iso2) == "BJ") {
                $viewData["bjId"] = $country->id;
            }
        }

        $viewData["phones"] = Phone::where('user_id', $viewData['userid'])->get();
        $viewData["statuses"] = Util::PHONE_STATUSES;

        return view('admin.whatsappnumbers', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Numéros WhatsApp', 
            'pagecardtilte' => 'Liste des numéros WhatsApp',
        ]);
    }

    public function addNumber(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $request->validate([
            'phonecountry' => 'required',
            'phone' => 'required|min:8|max:13'
        ]);

        $country = Country::find($request->phonecountry);
        
        if (!$country) {
            $alert["type"] = "danger";
            $alert["message"] = "Pays non identifié";
            return back()->with($alert);
        }

        $phonenumber = $country->phone_code . $request->phone;
        
        $result = $this->whatsAppService->startPhoneVerification(
            $request->session()->get('userid'),
            $request->phonecountry,
            $phonenumber
        );

        if ($result['success']) {
            $alert["type"] = "success";
            $alert["message"] = "Veuillez vérifier votre messagerie WhatsApp";
            $request->session()->put('phone_id', $result['phone_id']);
            return redirect()->route('admin.verify_phone')->with($alert);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            return back()->with($alert);
        }
    }

    public function verifyPhoneGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected() || !$request->session()->has('phone_id')) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $this->setViewData($request, $viewData);
        $viewData["phone_id"] = $request->session()->get('phone_id');

        return view('admin.verify_phone', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Vérification de numéro WhatsApp', 
            'pagecardtilte' => '',
        ]);
    }

    public function verifyPhonePost(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected() || !$request->session()->has('phone_id')) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $request->validate([
            'verification_code' => 'required'
        ]);

        $result = $this->whatsAppService->verifyPhone(
            $request->session()->get('userid'),
            $request->session()->get('phone_id'),
            $request->verification_code
        );

        if ($result['success']) {
            $request->session()->forget('phone_id');
            $alert["type"] = "success";
            $alert["message"] = "Numéro WhatsApp validé avec succès";
            return redirect()->route('admin.whatsapp_numbers')->with($alert);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            return back()->with($alert);
        }
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