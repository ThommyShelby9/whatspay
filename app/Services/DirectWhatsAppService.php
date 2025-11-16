<?php
// app/Services/DirectWhatsAppService.php - Version corrigée

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class DirectWhatsAppService
{
    protected $apiUrl;
    protected $apiToken;
    protected $maxRetries;
    protected $timeout;
    
    public function __construct()
    {
        $this->apiUrl = config('wasender.api_url', 'https://wasenderapi.com/api');
        $this->apiToken = config('wasender.personal_access_token');
        $this->maxRetries = 3;
        $this->timeout = 30;
        
        // Log de débogage pour vérifier le token
        Log::info('DirectWhatsAppService initialized', [
            'api_url' => $this->apiUrl,
            'token_length' => strlen($this->apiToken ?? ''),
            'token_start' => $this->apiToken ? substr($this->apiToken, 0, 10) : 'NULL'
        ]);
    }
    
    /**
     * Envoyer un message WhatsApp
     */
    public function sendMessage($recipient, $message)
    {
        // Vérification préliminaire du token
        if (empty($this->apiToken)) {
            Log::error('Token API WhatsApp non défini');
            return [
                'success' => false,
                'error' => 'Configuration API manquante'
            ];
        }
        
        try {
            Log::info('Tentative d\'envoi WhatsApp', [
                'recipient' => $this->maskPhoneNumber($recipient),
                'message_length' => strlen($message),
                'api_url' => $this->apiUrl
            ]);
            
            // Préparer les headers avec débogage
            $headers = [
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'WhatsPAY/1.0'
            ];
            
            Log::info('Headers préparés', [
                'authorization_length' => strlen($headers['Authorization']),
                'content_type' => $headers['Content-Type']
            ]);
            
            $payload = [
                'to' => $recipient,
                'text' => $message
            ];
            
            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->retry($this->maxRetries, 2000)
                ->post($this->apiUrl . '/send-message', $payload);
            
            // Log détaillé de la réponse
            Log::info('Réponse API reçue', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status()
                ];
            } else {
                // Analyse détaillée des erreurs d'authentification
                $errorDetails = $this->analyzeAuthError($response);
                
                Log::error("Échec d'envoi WhatsApp", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'recipient' => $this->maskPhoneNumber($recipient),
                    'error_analysis' => $errorDetails
                ]);
                
                return [
                    'success' => false,
                    'error' => $errorDetails['user_message'],
                    'status' => $response->status(),
                    'debug_info' => $errorDetails
                ];
            }
            
        } catch (\Exception $e) {
            Log::error("Exception lors de l'envoi WhatsApp", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'recipient' => $this->maskPhoneNumber($recipient)
            ]);
            
            return [
                'success' => false,
                'error' => 'Service temporairement indisponible'
            ];
        }
    }
    
    /**
     * Analyser les erreurs d'authentification
     */
    protected function analyzeAuthError($response)
    {
        $status = $response->status();
        $body = $response->body();
        
        $analysis = [
            'status_code' => $status,
            'raw_body' => $body,
            'user_message' => 'Erreur inconnue'
        ];
        
        switch ($status) {
            case 401:
                $analysis['issue'] = 'Token d\'authentification invalide';
                $analysis['user_message'] = 'Erreur de configuration API (401)';
                $analysis['suggestions'] = [
                    'Vérifier que WASENDERAPI_PERSONAL_ACCESS_TOKEN est correct',
                    'Vérifier que le token n\'a pas expiré',
                    'Vérifier le format du token (Bearer)',
                    'Tester le token manuellement avec curl'
                ];
                break;
                
            case 403:
                $analysis['issue'] = 'Accès interdit';
                $analysis['user_message'] = 'Accès non autorisé (403)';
                $analysis['suggestions'] = [
                    'Vérifier les permissions du token',
                    'Vérifier que le compte API est actif'
                ];
                break;
                
            case 400:
                $analysis['issue'] = 'Requête malformée';
                $analysis['user_message'] = 'Format de requête invalide (400)';
                break;
                
            case 404:
                $analysis['issue'] = 'Endpoint non trouvé';
                $analysis['user_message'] = 'Service non disponible (404)';
                $analysis['suggestions'] = [
                    'Vérifier l\'URL de l\'API: ' . $this->apiUrl
                ];
                break;
                
            default:
                $analysis['user_message'] = "Erreur HTTP $status";
        }
        
        // Essayer de décoder le JSON de la réponse
        $jsonResponse = json_decode($body, true);
        if ($jsonResponse) {
            $analysis['json_response'] = $jsonResponse;
            if (isset($jsonResponse['message'])) {
                $analysis['api_message'] = $jsonResponse['message'];
            }
        }
        
        return $analysis;
    }
    
    /**
     * Vérifier le statut de l'API avec diagnostic détaillé
     */
    public function checkApiStatus()
    {
        if (empty($this->apiToken)) {
            Log::error('Impossible de vérifier le statut: token manquant');
            return false;
        }
        
        $endpoints = ['/me', '/status', '/user', '/account'];
        
        foreach ($endpoints as $endpoint) {
            try {
                Log::info("Test de l'endpoint: {$endpoint}");
                
                $response = Http::withToken($this->apiToken)
                    ->timeout(10)
                    ->get($this->apiUrl . $endpoint);
                
                Log::info("Réponse de {$endpoint}", [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 200)
                ]);
                
                if ($response->successful()) {
                    Log::info("API accessible via {$endpoint}");
                    return true;
                }
                
            } catch (\Exception $e) {
                Log::warning("Échec du test {$endpoint}: " . $e->getMessage());
            }
        }
        
        Log::error('Tous les endpoints ont échoué');
        return false;
    }
    
    /**
     * Test d'authentification avec plusieurs méthodes
     */
    public function testAuthentication()
    {
        $results = [];
        
        // Test 1: Token comme Bearer
        $results['bearer_token'] = $this->testAuthMethod('Bearer', $this->apiToken);
        
        // Test 2: Token comme API Key dans headers
        $results['api_key_header'] = $this->testAuthMethod('ApiKey', $this->apiToken);
        
        // Test 3: Token dans le body
        $results['token_in_body'] = $this->testTokenInBody();
        
        return $results;
    }
    
    protected function testAuthMethod($method, $token)
    {
        try {
            $headers = match($method) {
                'Bearer' => ['Authorization' => 'Bearer ' . $token],
                'ApiKey' => ['X-API-Key' => $token],
                default => []
            };
            
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->get($this->apiUrl . '/me');
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    protected function testTokenInBody()
    {
        try {
            $response = Http::timeout(10)
                ->post($this->apiUrl . '/send-message', [
                    'token' => $this->apiToken,
                    'to' => '+1234567890',
                    'text' => 'test'
                ]);
            
            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 200)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    protected function maskPhoneNumber($phone)
    {
        if (strlen($phone) > 4) {
            return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 6) . substr($phone, -3);
        }
        return '***';
    }
}