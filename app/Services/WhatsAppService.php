<?php
// app/Services/WhatsAppService.php

namespace App\Services;

use App\Consts\Util;
use App\Models\Task;
use App\Models\Country;
use App\Models\Phone;
use App\Models\Phonehistory;
use App\Models\User;
use App\Models\Assignment;
use App\Traits\Utils;

class WhatsAppService
{
    use Utils;

    protected $directWhatsAppService;

    public function __construct(DirectWhatsAppService $directWhatsAppService)
    {
        $this->directWhatsAppService = $directWhatsAppService;
    }

    public function startPhoneVerification($userId, $phoneCountryId, $phoneNumber)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la génération du code',
            'phone_id' => null
        ];

        try {
            // Vérifier que l'utilisateur existe
            $user = User::find($userId);
            if (!$user) {
                $result['message'] = 'Session non identifiée [2]';
                return $result;
            }

            // Vérifier que le pays existe
            $country = Country::find($phoneCountryId);
            if (!$country) {
                $result['message'] = 'Pays non identifié';
                return $result;
            }

            // Formatage du numéro de téléphone
            if (!str_starts_with($phoneNumber, $country->phone_code)) {
                $phoneNumber = $country->phone_code . $phoneNumber;
            }

            // Vérifier si le numéro existe déjà
            $phone = Phone::where('user_id', $userId)
                ->where("phonecountry_id", $phoneCountryId)
                ->where("phone", $phoneNumber)
                ->first();

            // Générer un code de vérification à 6 chiffres
            $date = gmdate('Y-m-d H:i:s');
            $code = $this->generateKey(6, 7);
            $message = "Pour valider votre numéro WhatsApp, veuillez bien saisir le code suivant : " . $code;

            $phoneId = "";
            if (!$phone) {
                $phoneId = $this->getId();
                Phone::create([
                    'id' => $phoneId,
                    'phone' => $phoneNumber,
                    'phonecountry_id' => $phoneCountryId,
                    'status' => Util::PHONE_STATUSES["PENDING"]["label"],
                    'valcode' => $code,
                    'valcode_gendate' => $date,
                    'user_id' => $userId
                ]);
            } else {
                $phoneId = $phone->id;
                Phone::where('id', $phoneId)->update([
                    'status' => Util::PHONE_STATUSES["PENDING"]["label"],
                    'valcode' => $code,
                    'valcode_gendate' => $date,
                ]);
            }

            // Envoyer le code via l'API directe
            $apiResult = $this->directWhatsAppService->sendMessage($phoneNumber, $message);
            
            // Enregistrer l'historique
            $historyId = $this->getId();
            Phonehistory::create([
                'id' => $historyId,
                'history' => json_encode([
                    "phone" => $phoneNumber,
                    "message" => $message,
                    "result" => $apiResult
                ]),
                'phone_id' => $phoneId
            ]);

            // Vérifier le résultat
            if ($apiResult['success']) {
                $result['success'] = true;
                $result['message'] = 'Veuillez vérifier votre messagerie WhatsApp';
                $result['phone_id'] = $phoneId;
            } else {
                $result['message'] = 'Une erreur est survenue lors de l\'envoi du message. Erreur: ' . 
                    (isset($apiResult['error']) ? $apiResult['error'] : 'Inconnue');
            }
        } catch (\Exception $exception) {
            \Log::error('Erreur WhatsApp Service: ' . $exception->getMessage());
            $result['message'] = 'Une erreur est survenue. Veuillez réessayer plus tard.';
        }

        return $result;
    }

    public function verifyPhone($userId, $phoneId, $code, $phoneCountryId = null, $phoneNumber = null)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la validation'
        ];

        // Vérifier que l'utilisateur existe
        $user = User::find($userId);
        if (!$user) {
            $result['message'] = 'Session non identifiée [2]';
            return $result;
        }

        $query = Phone::where('id', $phoneId)
            ->where('user_id', $userId)
            ->where('valcode', $code);

        // Ajouter des conditions supplémentaires si fournies
        if ($phoneCountryId) {
            $query->where('phonecountry_id', $phoneCountryId);
        }

        if ($phoneNumber) {
            $query->where('phone', $phoneNumber);
        }

        $phone = $query->first();

        if ($phone) {
            // Valider le numéro
            Phone::where('id', $phoneId)->update([
                'status' => Util::PHONE_STATUSES["ACTIVE"]["label"]
            ]);
            
            $result['success'] = true;
            $result['message'] = 'Numéro WhatsApp validé avec succès';
        } else {
            $result['message'] = 'Nous n\'avons pas pu valider votre numéro WhatsApp. Veuillez réessayer plus tard.';
        }

        return $result;
    }

    public function sendMessage($recipient, $message)
    {
        return $this->directWhatsAppService->sendMessage($recipient, $message);
    }

    /**
     * Send campaign notification to an influencer
     */
    public function sendCampaignNotification(Task $campaign, User $user)
    {
        // Get active WhatsApp numbers for this user
        $phones = Phone::where('user_id', $user->id)
            ->where('status', Util::PHONE_STATUSES["ACTIVE"]["label"])
            ->get();
            
        if ($phones->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucun numéro WhatsApp actif trouvé pour cet utilisateur'
            ];
        }
        
        $message = "🔔 *Nouvelle campagne disponible sur WhatsPAY* 🔔\n\n";
        $message .= "Bonjour " . $user->firstname . ",\n\n";
        $message .= "Une nouvelle campagne \"" . $campaign->title . "\" est disponible.\n";
        $message .= "Détails: " . substr($campaign->description, 0, 100) . "...\n\n";
        $message .= "Rémunération: " . number_format($campaign->payment_amount, 0, ',', ' ') . " FCFA\n\n";
        $message .= "Connectez-vous sur WhatsPAY pour plus de détails et postuler.\n";
        $message .= config('app.url') . "/influencer/campaigns/" . $campaign->id;
        
        $results = [];
        foreach ($phones as $phone) {
            $apiResult = $this->directWhatsAppService->sendMessage($phone->phone, $message);
            
            // Save to history
            $historyId = $this->getId();
            Phonehistory::create([
                'id' => $historyId,
                'history' => json_encode([
                    "phone" => $phone->phone,
                    "message" => $message,
                    "result" => $apiResult,
                    "type" => "campaign_notification",
                    "campaign_id" => $campaign->id
                ]),
                'phone_id' => $phone->id
            ]);
            
            $results[] = [
                'phone' => $phone->phone,
                'success' => isset($apiResult["success"]) && $apiResult["success"] == true
            ];
        }
        
        return [
            'success' => collect($results)->contains('success', true),
            'results' => $results
        ];
    }
    
    /**
     * Send campaign screenshot reminder to an influencer
     */
    public function sendCampaignScreenshotReminder(Assignment $assignment)
    {
        $user = User::find($assignment->user_id);
        $campaign = Task::find($assignment->campaign_id);
        
        if (!$user || !$campaign) {
            return [
                'success' => false,
                'message' => 'Utilisateur ou campagne non trouvé'
            ];
        }
        
        // Get active WhatsApp numbers for this user
        $phones = Phone::where('user_id', $user->id)
            ->where('status', Util::PHONE_STATUSES["ACTIVE"]["label"])
            ->get();
            
        if ($phones->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Aucun numéro WhatsApp actif trouvé pour cet utilisateur'
            ];
        }
        
        $message = "⏰ *Rappel: Capture d'écran pour la campagne* ⏰\n\n";
        $message .= "Bonjour " . $user->firstname . ",\n\n";
        $message .= "N'oubliez pas de soumettre votre capture d'écran pour la campagne \"" . $campaign->title . "\".\n\n";
        $message .= "Vous devez le faire avant demain pour recevoir votre paiement.\n\n";
        $message .= "Connectez-vous sur WhatsPAY pour soumettre votre capture d'écran:\n";
        $message .= config('app.url') . "/influencer/campaigns/assignments";
        
        $results = [];
        foreach ($phones as $phone) {
            $apiResult = $this->directWhatsAppService->sendMessage($phone->phone, $message);
            
            // Save to history
            $historyId = $this->getId();
            Phonehistory::create([
                'id' => $historyId,
                'history' => json_encode([
                    "phone" => $phone->phone,
                    "message" => $message,
                    "result" => $apiResult,
                    "type" => "screenshot_reminder",
                    "campaign_id" => $campaign->id,
                    "assignment_id" => $assignment->id
                ]),
                'phone_id' => $phone->id
            ]);
            
            $results[] = [
                'phone' => $phone->phone,
                'success' => isset($apiResult["success"]) && $apiResult["success"] == true
            ];
        }
        
        return [
            'success' => collect($results)->contains('success', true),
            'results' => $results
        ];
    }
}