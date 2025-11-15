<?php
// app/Http/Controllers/Web/Admin/WhatsAppMessagingController.php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Phone;
use App\Models\User;
use App\Models\Task;
use App\Services\WhatsAppService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppMessagingController extends Controller
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
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        $this->setViewData($request, $viewData);
        
        // Get list of users with verified WhatsApp numbers
        $viewData['influencers'] = User::select(
                'users.id',
                'users.firstname',
                'users.lastname',
                'users.email',
                DB::raw('COUNT(phones.id) as phone_count')
            )
            ->join('phones', 'users.id', '=', 'phones.user_id')
            ->where('phones.status', 'ACTIVE')
            ->groupBy('users.id', 'users.firstname', 'users.lastname', 'users.email')
            ->get();
        
        // Get active campaigns for filtering
        $viewData['campaigns'] = Task::where('status', 'ACTIVE')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.whatsapp_messaging', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin',
            'pagetilte' => 'Messages WhatsApp',
            'pagecardtilte' => 'Envoi de messages en masse',
        ]);
    }
    
    public function sendMassMessage(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        $request->validate([
            'message' => 'required|string',
            'recipients' => 'required|array',
            'recipients.*' => 'exists:users,id',
        ]);
        
        $message = $request->input('message');
        $recipients = $request->input('recipients');
        $successCount = 0;
        $failedCount = 0;
        $errors = [];
        
        // Process each recipient
        foreach ($recipients as $userId) {
            $user = User::find($userId);
            
            if (!$user) {
                $failedCount++;
                continue;
            }
            
            // Personnaliser le message pour chaque utilisateur
            $personalizedMessage = str_replace(
                ['{prenom}', '{nom}'], 
                [$user->firstname, $user->lastname], 
                $message
            );
            
            // Get active WhatsApp numbers for this user
            $phones = Phone::where('user_id', $userId)
                ->where('status', 'ACTIVE')
                ->get();
                
            if ($phones->isEmpty()) {
                $failedCount++;
                $errors[] = "Aucun numéro WhatsApp actif trouvé pour {$user->firstname} {$user->lastname}";
                continue;
            }
            
            $userSuccess = false;
            foreach ($phones as $phone) {
                try {
                    // Send the message
                    $result = $this->whatsAppService->sendMessage($phone->phone, $personalizedMessage);
                    
                    if (isset($result['success']) && $result['success']) {
                        $userSuccess = true;
                    }
                } catch (\Exception $e) {
                    \Log::error('Erreur d\'envoi WhatsApp: ' . $e->getMessage());
                }
            }
            
            if ($userSuccess) {
                $successCount++;
            } else {
                $failedCount++;
                $errors[] = "Échec d'envoi à {$user->firstname} {$user->lastname}";
            }
        }
        
        // Create notification log
        $this->createLogg([
            'message' => $message,
            'recipients' => $recipients,
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors
        ], "whatsapp_mass_message_", "");
        
        return redirect()->route('admin.whatsapp_messaging')->with([
            'type' => 'success',
            'message' => "Message envoyé avec succès à {$successCount} diffuseur(s). {$failedCount} échec(s)."
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