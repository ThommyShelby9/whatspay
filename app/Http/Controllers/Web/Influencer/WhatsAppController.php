<?php
// File: app/Http/Controllers/Web/Influencer/WhatsAppController.php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\WhatsAppService;
use App\Traits\Utils;
use App\Models\Phone;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        
        // Récupérer les numéros WhatsApp de l'utilisateur
        $viewData["whatsappNumbers"] = Phone::select(
                'phones.id',
                'phones.phone',
                'phones.status',
                'phones.created_at',
                'countries.phone_code',
                'countries.name as country_name'
            )
            ->leftJoin('countries', 'phones.phonecountry_id', '=', 'countries.id')
            ->where('phones.user_id', $userId)
            ->orderBy('phones.created_at', 'desc')
            ->get();
            
        // Récupérer les pays pour le formulaire d'ajout
        $viewData["countries"] = Country::select('id', 'name', 'phone_code')
            ->where('enabled', true)
            ->orderBy('name')
            ->get();
        
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
    
    public function addPhone(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        // Valider les données
        $request->validate([
            'country_code' => 'required|string',
            'phone_number' => 'required|string',
        ]);
        
        // Extraire l'ID du pays à partir du code pays
        $countryCode = $request->input('country_code');
        $country = Country::where('phone_code', $countryCode)->first();
        
        if (!$country) {
            return redirect()->route('influencer.whatsapp')
                ->with('type', 'danger')
                ->with('message', 'Pays non trouvé');
        }
        
        // Formater le numéro de téléphone
        $phoneNumber = $request->input('phone_number');
        
        // Démarrer la vérification
        $result = $this->whatsAppService->startPhoneVerification(
            $userId,
            $country->id,
            $phoneNumber
        );
        
        if ($result['success']) {
            return redirect()->route('influencer.whatsapp')
                ->with('type', 'success')
                ->with('message', $result['message'])
                ->with('phone_id', $result['phone_id']);
        } else {
            return redirect()->route('influencer.whatsapp')
                ->with('type', 'danger')
                ->with('message', $result['message']);
        }
    }
    
    public function verifyPhone(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        // Valider les données
        $request->validate([
            'phone_id' => 'required|string',
            'verification_code' => 'required|string|size:6',
        ]);
        
        $phoneId = $request->input('phone_id');
        $code = $request->input('verification_code');
        
        // Vérifier le numéro
        $result = $this->whatsAppService->verifyPhone(
            $userId,
            $phoneId,
            $code
        );
        
        if ($result['success']) {
            return redirect()->route('influencer.whatsapp')
                ->with('type', 'success')
                ->with('message', 'Numéro vérifié avec succès');
        } else {
            return redirect()->route('influencer.whatsapp')
                ->with('type', 'danger')
                ->with('message', $result['message']);
        }
    }
    
    public function deletePhone(Request $request, $id)
    {
        $userId = $request->session()->get('userid');
        
        // Vérifier que le numéro appartient à l'utilisateur
        $phone = Phone::where('id', $id)
            ->where('user_id', $userId)
            ->first();
            
        if (!$phone) {
            return redirect()->route('influencer.whatsapp')
                ->with('type', 'danger')
                ->with('message', 'Numéro non trouvé');
        }
        
        // Supprimer le numéro
        Phone::where('id', $id)->delete();
        
        return redirect()->route('influencer.whatsapp')
            ->with('type', 'success')
            ->with('message', 'Numéro supprimé avec succès');
    }
    
    public function resendCode(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        // Valider les données
        $request->validate([
            'phone_id' => 'required|string',
        ]);
        
        $phoneId = $request->input('phone_id');
        
        // Récupérer le numéro
        $phone = Phone::where('id', $phoneId)
            ->where('user_id', $userId)
            ->first();
            
        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Numéro non trouvé'
            ]);
        }
        
        // Générer un nouveau code et l'envoyer
        $result = $this->whatsAppService->startPhoneVerification(
            $userId,
            $phone->phonecountry_id,
            $phone->phone
        );
        
        return response()->json([
            'success' => $result['success'],
            'message' => $result['message']
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