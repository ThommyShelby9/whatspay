<?php
// app/Services/DirectWhatsAppService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DirectWhatsAppService
{
    protected $apiUrl = 'https://wasenderapi.com/api';
    protected $apiToken;
    
    public function __construct()
    {
        $this->apiToken = config('wasender.personal_access_token');
    }
    
    /**
     * Envoyer un message WhatsApp
     * 
     * @param string $recipient Numéro de téléphone (format international)
     * @param string $message Texte du message
     * @return array Réponse de l'API
     */
    public function sendMessage($recipient, $message)
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(30)
                ->retry(3, 5000) // 3 tentatives avec 5 secondes d'intervalle
                ->post($this->apiUrl . '/send-message', [
                    'to' => $recipient,
                    'text' => $message
                ]);
            
            if ($response->successful()) {
                Log::info("Message WhatsApp envoyé avec succès à {$recipient}");
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            } else {
                Log::error("Échec d'envoi WhatsApp: " . $response->body());
                return [
                    'success' => false,
                    'error' => $response->body(),
                    'status' => $response->status()
                ];
            }
        } catch (\Exception $e) {
            Log::error("Exception lors de l'envoi WhatsApp: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Vérifier le statut de l'API
     * 
     * @return boolean
     */
    public function checkApiStatus()
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->timeout(10)
                ->get($this->apiUrl . '/status');
                
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Échec de vérification du statut API: " . $e->getMessage());
            return false;
        }
    }
}