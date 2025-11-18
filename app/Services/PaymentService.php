<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Wallet;
use App\Traits\Utils;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PaymentService
{
    use Utils;
    
    protected $walletService;
    protected $payPlusBaseUrl;
    protected $payPlusApiKey;
    protected $payPlusApiToken;
    
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->payPlusBaseUrl = config('payplus.base_url', 'https://app.payplus.africa');
        $this->payPlusApiKey = config('payplus.api_key', '57DD7H4RBP8WVAM3D');
        $this->payPlusApiToken = config('payplus.api_token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZF9hcHAiOiI0NjgyIiwiaWRfYWJvbm5lIjoxMDc4MCwiZGF0ZWNyZWF0aW9uX2FwcCI6IjIwMjUtMTEtMDEgMDI6MTU6MTIifQ.aOirgkjSysUBnUUAQG6m9eJpZu0WAz1OInYbYAqX_rY');
    }
    
    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods()
    {
        try {
            if (Schema::hasTable('payment_methods')) {
                return PaymentMethod::where('status', 'ACTIVE')
                    ->orderBy('name')
                    ->get();
            }
            return collect([]);
        } catch (\Exception $e) {
            Log::error('Error getting payment methods: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    /**
     * Initiate deposit for announcer - According to official PayPlus documentation
     *
     * @param string $userId ID of the user
     * @param float $amount Amount to deposit
     * @param string $customerPhone Customer phone number
     * @param bool $useRedirect Whether to use redirect flow or straight flow
     * @return array Result including success status, message, and redirect URL
     */
    public function initiateDeposit($userId, $amount, $customerPhone, $useRedirect = true)
    {
        Log::info('=== DÉBUT initiateDeposit (Documentation Officielle) ===', [
            'userId' => $userId,
            'amount' => $amount,
            'customerPhone' => $customerPhone,
            'useRedirect' => $useRedirect,
        ]);
        
        try {
            // Validation
            if ($amount < 1000) {
                Log::warning('Montant trop faible', ['amount' => $amount]);
                return [
                    'success' => false,
                    'message' => 'Le montant minimum de dépôt est de 1000 FCFA'
                ];
            }
            
            Log::info('Validation montant OK');
            
            // Vérification des configurations PayPlus
            if (!$this->payPlusApiKey || !$this->payPlusApiToken) {
                Log::error('Configuration PayPlus manquante', [
                    'has_api_key' => !empty($this->payPlusApiKey),
                    'has_token' => !empty($this->payPlusApiToken)
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Configuration PayPlus manquante - Contactez l\'administrateur'
                ];
            }
            
            // Create payment transaction record
            $transactionId = $this->getId();
            $externalId = 'DEP-' . time() . '-' . substr($transactionId, 0, 8);
            
            Log::info('Création transaction', [
                'transaction_id' => $transactionId,
                'external_id' => $externalId
            ]);
            
            // Vérifier si la table payment_transactions existe
            if (!Schema::hasTable('payment_transactions')) {
                Log::error('Table payment_transactions manquante');
                
                return [
                    'success' => false,
                    'message' => 'Configuration de base de données incomplète - Exécutez les migrations'
                ];
            }
            
            // Vérifier si la route callback existe
            try {
                $callbackUrl = route('payment.callback', ['transaction' => $transactionId]);
                Log::info('Callback URL générée', ['url' => $callbackUrl]);
            } catch (\Exception $e) {
                Log::error('Erreur génération callback URL', ['error' => $e->getMessage()]);
                $callbackUrl = config('app.url') . '/payment/callback/' . $transactionId;
            }
            
            $paymentTransaction = PaymentTransaction::create([
                'id' => $transactionId,
                'user_id' => $userId,
                'payment_method_id' => null,
                'amount' => $amount,
                'currency' => 'XOF',
                'status' => 'PENDING',
                'reference' => $externalId,
                'expires_at' => Carbon::now()->addHour(),
                'callback_url' => $callbackUrl,
                'payload' => json_encode([
                    'type' => 'deposit',
                    'user_id' => $userId,
                    'amount' => $amount,
                    'phone' => $customerPhone
                ])
            ]);
            
            Log::info('Transaction créée en DB', ['id' => $paymentTransaction->id]);
            
            // ✅ Payload selon la documentation officielle PayPlus
            // Récupérer les infos utilisateur pour un payload plus complet
            $user = \App\Models\User::find($userId);

            // Nettoyer et formater le numéro de téléphone pour PayPlus
            // PayPlus accepte: +CCXXXXXXXXX (CC = country code)
            $cleanPhone = preg_replace('/[^0-9+]/', '', $customerPhone);

            // Si le numéro commence déjà par un indicatif pays (2-3 chiffres), le garder
            // Sinon, ajouter l'indicatif par défaut (229 pour Bénin)
            if (!str_starts_with($cleanPhone, '+')) {
                // Vérifier si le numéro commence déjà par un indicatif pays valide (225, 229, 237, etc.)
                if (!preg_match('/^(22[0-9]|23[0-9]|24[0-9]|25[0-9])/', $cleanPhone)) {
                    // Pas d'indicatif pays détecté, ajouter 229 par défaut
                    $cleanPhone = '229' . $cleanPhone;
                }
                $cleanPhone = '+' . $cleanPhone;
            }

            Log::info('Numéro de téléphone formaté', [
                'original' => $customerPhone,
                'cleaned' => $cleanPhone
            ]);

            $payload = [
                'commande' => [
                    'invoice' => [
                        'items' => [
                            [
                                'name' => 'Rechargement compte WhatsPAY',
                                'description' => 'Ajout de fonds au portefeuille',
                                'quantity' => 1,
                                'unit_price' => $amount,
                                'total_price' => $amount
                            ]
                        ],
                        'total_amount' => $amount,
                        'devise' => 'XOF',
                        'description' => 'Rechargement compte WhatsPAY',
                        'customer' => $cleanPhone,
                        'customer_firstname' => $user->firstname ?? 'Client',
                        'customer_lastname' => $user->lastname ?? 'WhatsPAY',
                        'customer_email' => $user->email ?? 'client@whatspay.africa',
                        'external_id' => $externalId,
                        'otp' => ''
                    ],
                    'actions' => [
                        'cancel_url' => route('announcer.wallet') . '?status=cancelled',
                        'return_url' => route('announcer.wallet') . '?status=success',
                        'callback_url' => $callbackUrl,
                        'callback_url_method' => 'post_json'
                    ],
                    'custom_data' => [
                        'transaction_id' => $transactionId,
                        'user_id' => $userId,
                        'hash' => hash('sha256', $transactionId . $amount . $userId)
                    ]
                ]
            ];
            
            Log::info('Payload PayPlus préparé (doc officielle)', $payload);
            
            // ✅ Endpoint selon la documentation officielle
            $endpoint = $useRedirect ? 
                '/pay/v01/redirect/checkout-invoice/create' : 
                '/pay/v01/straight/checkout-invoice/create';
            
            // ✅ Headers selon la documentation officielle
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->payPlusApiToken,
                'Apikey' => $this->payPlusApiKey
            ];
            
            // URLs de base à tester (selon la documentation et les variations connues)
            $baseUrlsToTry = [
                $this->payPlusBaseUrl,
                'https://api.payplus.africa',
                'https://payplus.africa',
                'https://gateway.payplus.africa'
            ];
            
            $lastResponse = null;
            $lastError = null;
            
            foreach ($baseUrlsToTry as $baseUrl) {
                $fullUrl = $baseUrl . $endpoint;
                
                Log::info('Tentative PayPlus API', [
                    'url' => $fullUrl,
                    'base_url' => $baseUrl,
                    'endpoint' => $endpoint
                ]);
                
                try {
                    $response = Http::timeout(30)
                        ->withHeaders($headers)
                        ->post($fullUrl, $payload);
                    
                    $lastResponse = $response;
                    $statusCode = $response->status();
                    
                    Log::info('Réponse PayPlus', [
                        'base_url' => $baseUrl,
                        'status' => $statusCode,
                        'successful' => $response->successful(),
                        'body' => $response->body()
                    ]);
                    
                    // Si pas 404, on a trouvé la bonne URL
                    if ($statusCode !== 404) {
                        if ($response->successful()) {
                            $responseData = $response->json();
                            
                            Log::info('Réponse PayPlus décodée', $responseData);
                            
                            // ✅ Vérification selon la documentation : response_code = "00" = succès
                            if (isset($responseData['response_code']) && $responseData['response_code'] === '00') {

                                // Update transaction with PayPlus response
                                $paymentTransaction->update([
                                    'gateway_response' => json_encode($responseData)
                                ]);

                                Log::info('✅ Succès PayPlus', [
                                    'base_url' => $baseUrl,
                                    'response_code' => $responseData['response_code'],
                                    'token' => $responseData['token'] ?? 'N/A',
                                    'redirect_url' => $responseData['response_text'] ?? 'N/A',
                                    'full_response' => $responseData
                                ]);

                                // Log pour debug: vérifier si tous les champs sont présents
                                if (!isset($responseData['response_text']) || empty($responseData['response_text'])) {
                                    Log::warning('⚠️ PayPlus response_text manquant ou vide', [
                                        'response_data' => $responseData
                                    ]);
                                }

                                return [
                                    'success' => true,
                                    'message' => 'Redirection vers la passerelle de paiement',
                                    'redirect_url' => $responseData['response_text'],
                                    'transaction_id' => $transactionId,
                                    'token' => $responseData['token'] ?? null,
                                    'debug_response' => config('app.debug') ? $responseData : null
                                ];
                                
                            } else {
                                // Payment initiation failed selon la doc
                                Log::error('❌ Échec PayPlus', [
                                    'base_url' => $baseUrl,
                                    'response_code' => $responseData['response_code'] ?? 'N/A',
                                    'description' => $responseData['description'] ?? 'N/A'
                                ]);
                                
                                $paymentTransaction->update([
                                    'status' => 'FAILED',
                                    'gateway_response' => json_encode($responseData)
                                ]);
                                
                                $errorMessage = $responseData['description'] ?? 'Erreur lors de l\'initialisation du paiement';
                                
                                if (config('app.debug')) {
                                    $errorMessage .= ' (Code: ' . ($responseData['response_code'] ?? 'N/A') . ')';
                                }
                                
                                return [
                                    'success' => false,
                                    'message' => $errorMessage
                                ];
                            }
                            
                        } else {
                            // Erreur HTTP mais pas 404
                            $errorMessage = $this->getErrorMessageFromStatusCode($statusCode);
                            Log::error('Erreur HTTP PayPlus', [
                                'base_url' => $baseUrl,
                                'status' => $statusCode,
                                'body' => $response->body()
                            ]);
                            
                            return [
                                'success' => false,
                                'message' => $errorMessage
                            ];
                        }
                    }
                    
                } catch (\Exception $e) {
                    $lastError = $e->getMessage();
                    Log::warning('Erreur tentative PayPlus', [
                        'base_url' => $baseUrl,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
            
            // Si on arrive ici, toutes les URLs ont échoué
            Log::error('🚫 TOUTES LES URLS PAYPLUS ONT ÉCHOUÉ', [
                'last_response_status' => $lastResponse ? $lastResponse->status() : 'N/A',
                'last_response_body' => $lastResponse ? $lastResponse->body() : 'N/A',
                'last_error' => $lastError
            ]);
            
            return [
                'success' => false,
                'message' => 'Service PayPlus temporairement indisponible. Veuillez réessayer dans quelques minutes.'
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception dans initiateDeposit', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = config('app.debug') ? 
                'Erreur PayPlus: ' . $e->getMessage() :
                'Une erreur est survenue. Veuillez réessayer.';
            
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }
    }
    
    /**
     * Get error message from HTTP status code
     */
    private function getErrorMessageFromStatusCode($statusCode)
    {
        return match($statusCode) {
            400 => 'Données de paiement invalides. Vérifiez le montant et le téléphone.',
            401, 403 => 'Erreur d\'authentification PayPlus. Contactez le support.',
            404 => 'Service PayPlus non trouvé. Contactez le support.',
            500, 502, 503 => 'Erreur serveur PayPlus. Veuillez réessayer.',
            default => 'Erreur de communication avec PayPlus (Code: ' . $statusCode . ')'
        };
    }
    
    /**
     * Initiate withdrawal for influencer - According to official PayPlus documentation
     *
     * @param string $userId ID of the user
     * @param float $amount Amount to withdraw
     * @param string $customerPhone Customer phone number
     * @param bool $useInternalWallet Use internal wallet or direct mobile money
     * @return array Result
     */
    public function initiateWithdrawal($userId, $amount, $customerPhone, $useInternalWallet = false)
    {
        try {
            Log::info('=== DÉBUT initiateWithdrawal ===', [
                'userId' => $userId,
                'amount' => $amount,
                'customerPhone' => $customerPhone,
                'useInternalWallet' => $useInternalWallet
            ]);
            
            // Check wallet balance
            $balance = $this->walletService->getBalance($userId);
            
            if ($balance < $amount) {
                return [
                    'success' => false,
                    'message' => 'Solde insuffisant pour effectuer ce retrait'
                ];
            }
            
            if ($amount < 500) { // Minimum 500 XOF withdrawal
                return [
                    'success' => false,
                    'message' => 'Le montant minimum de retrait est de 500 FCFA'
                ];
            }
            
            // Create withdrawal transaction
            $transactionId = $this->getId();
            $externalId = 'WTH-' . time() . '-' . substr($transactionId, 0, 8);
            
            // Generate callback URL
            try {
                $callbackUrl = route('payment.callback.withdrawal', ['transaction' => $transactionId]);
            } catch (\Exception $e) {
                $callbackUrl = config('app.url') . '/payment/callback/withdrawal/' . $transactionId;
            }
            
            $paymentTransaction = PaymentTransaction::create([
                'id' => $transactionId,
                'user_id' => $userId,
                'amount' => -$amount, // Negative for withdrawal
                'currency' => 'XOF',
                'status' => 'PENDING',
                'reference' => $externalId,
                'expires_at' => Carbon::now()->addHour(),
                'callback_url' => $callbackUrl,
                'payload' => json_encode([
                    'type' => 'withdrawal',
                    'user_id' => $userId,
                    'amount' => $amount,
                    'phone' => $customerPhone,
                    'use_internal_wallet' => $useInternalWallet
                ])
            ]);
            
            // ✅ Payload selon la documentation officielle PayPlus pour les retraits
            $payload = [
                'commande' => [
                    'amount' => $amount,
                    'customer' => $customerPhone,
                    'custom_data' => [
                        'transaction_id' => $transactionId,
                        'user_id' => $userId,
                        'hash' => hash('sha256', $transactionId . $amount . $userId)
                    ],
                    'callback_url' => $callbackUrl,
                    'callback_url_method' => 'post_json',
                    'external_id' => $externalId,
                    'network' => ''
                ]
            ];
            
            // Add top_up_wallet for internal wallet selon la doc
            if ($useInternalWallet) {
                $payload['commande']['top_up_wallet'] = 1;
            }
            
            Log::info('Payload withdrawal PayPlus', $payload);
            
            // ✅ Choose endpoint selon la documentation officielle
            $endpoint = $useInternalWallet ? 
                '/pay/v01/withdrawal/create' : 
                '/pay/v01/straight/payout';
            
            // Headers selon la documentation
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->payPlusApiToken,
                'Apikey' => $this->payPlusApiKey
            ];
            
            // URLs de base à tester
            $baseUrlsToTry = [
                $this->payPlusBaseUrl,
                'https://api.payplus.africa',
                'https://payplus.africa'
            ];
            
            foreach ($baseUrlsToTry as $baseUrl) {
                $fullUrl = $baseUrl . $endpoint;
                
                Log::info('Tentative withdrawal PayPlus', [
                    'url' => $fullUrl,
                    'base_url' => $baseUrl
                ]);
                
                try {
                    $response = Http::timeout(30)
                        ->withHeaders($headers)
                        ->post($fullUrl, $payload);
                    
                    $statusCode = $response->status();
                    
                    Log::info('Réponse withdrawal PayPlus', [
                        'base_url' => $baseUrl,
                        'status' => $statusCode,
                        'body' => $response->body()
                    ]);
                    
                    if ($statusCode !== 404) {
                        if ($response->successful()) {
                            $responseData = $response->json();
                            
                            // ✅ Vérification selon la doc : response_code = "00" = succès
                            if (isset($responseData['response_code']) && $responseData['response_code'] === '00') {
                                
                                // Deduct from wallet immediately (will be reversed if withdrawal fails)
                                $deductResult = $this->walletService->deductFunds(
                                    $userId,
                                    $amount,
                                    'Retrait vers ' . $customerPhone,
                                    $transactionId
                                );
                                
                                if (!$deductResult['success']) {
                                    // Si échec déduction, supprimer la transaction
                                    $paymentTransaction->delete();
                                    return $deductResult;
                                }
                                
                                // Update transaction
                                $paymentTransaction->update([
                                    'status' => 'PROCESSING',
                                    'gateway_response' => json_encode($responseData)
                                ]);
                                
                                Log::info('✅ Withdrawal PayPlus initié', [
                                    'transaction_id' => $transactionId,
                                    'token' => $responseData['token'] ?? 'N/A'
                                ]);
                                
                                return [
                                    'success' => true,
                                    'message' => 'Demande de retrait initiée. Vous recevrez une confirmation sous peu.',
                                    'transaction_id' => $transactionId,
                                    'token' => $responseData['token'] ?? null
                                ];
                                
                            } else {
                                Log::error('❌ Échec withdrawal PayPlus', $responseData);
                                
                                return [
                                    'success' => false,
                                    'message' => $responseData['description'] ?? 'Erreur lors de l\'initialisation du retrait'
                                ];
                            }
                        } else {
                            $errorMessage = $this->getErrorMessageFromStatusCode($statusCode);
                            return [
                                'success' => false,
                                'message' => $errorMessage
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Erreur tentative withdrawal', [
                        'base_url' => $baseUrl,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
            
            Log::error('🚫 Toutes les URLs withdrawal ont échoué');
            
            return [
                'success' => false,
                'message' => 'Service de retrait PayPlus temporairement indisponible'
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception withdrawal', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return [
                'success' => false,
                'message' => config('app.debug') ? 
                    'Erreur withdrawal: ' . $e->getMessage() :
                    'Une erreur est survenue. Veuillez réessayer.'
            ];
        }
    }
    
    /**
     * Check transaction status with PayPlus - According to official documentation
     */
    public function checkTransactionStatus($transactionId)
    {
        try {
            $transaction = PaymentTransaction::find($transactionId);
            
            if (!$transaction) {
                return [
                    'success' => false,
                    'message' => 'Transaction non trouvée'
                ];
            }
            
            $gatewayResponse = json_decode($transaction->gateway_response, true);
            $payPlusToken = $gatewayResponse['token'] ?? null;
            
            if (!$payPlusToken) {
                return [
                    'success' => false,
                    'message' => 'Token PayPlus non trouvé'
                ];
            }
            
            // Determine endpoint based on transaction type selon la doc
            $payload = json_decode($transaction->payload, true);
            $isWithdrawal = $payload['type'] === 'withdrawal';
            
            // ✅ Endpoints selon la documentation officielle
            $endpoint = $isWithdrawal ? 
                '/pay/v01/withdrawal/confirm' : 
                '/pay/v01/redirect/checkout-invoice/confirm';
                
            $paramName = $isWithdrawal ? 'withdrawalToken' : 'invoiceToken';
            
            // Headers selon la doc
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->payPlusApiToken,
                'Apikey' => $this->payPlusApiKey
            ];
            
            // URLs de base à tester
            $baseUrlsToTry = [
                $this->payPlusBaseUrl,
                'https://api.payplus.africa',
                'https://payplus.africa'
            ];
            
            foreach ($baseUrlsToTry as $baseUrl) {
                $fullUrl = $baseUrl . $endpoint;
                
                try {
                    $response = Http::timeout(15)
                        ->withHeaders($headers)
                        ->get($fullUrl, [
                            $paramName => $payPlusToken
                        ]);
                    
                    if ($response->successful()) {
                        $responseData = $response->json();
                        
                        Log::info('Status check PayPlus', [
                            'transaction_id' => $transactionId,
                            'response' => $responseData
                        ]);
                        
                        // ✅ Selon la doc : description peut être "pending", "completed", "notcompleted"
                        $status = $responseData['description'] ?? 'unknown';
                        
                        return [
                            'success' => true,
                            'status' => $status,
                            'response_code' => $responseData['response_code'] ?? 'N/A',
                            'payplus_response' => $responseData,
                            'local_status' => $transaction->status
                        ];
                    }
                } catch (\Exception $e) {
                    Log::warning('Erreur status check', [
                        'base_url' => $baseUrl,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
            
            return [
                'success' => false,
                'message' => 'Impossible de vérifier le statut'
            ];
            
        } catch (\Exception $e) {
            Log::error('Exception status check', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ];
        }
    }
    
    /**
     * Process PayPlus callback for deposits
     */
    public function processDepositCallback($transactionId, $data)
    {
        try {
            Log::info('Processing deposit callback', [
                'transaction_id' => $transactionId,
                'data' => $data
            ]);
            
            $transaction = PaymentTransaction::find($transactionId);
            
            if (!$transaction) {
                Log::error('Deposit callback: Transaction not found', [
                    'transaction_id' => $transactionId,
                    'data' => $data
                ]);
                return false;
            }
            
            // Update transaction with callback data
            $existingResponse = json_decode($transaction->gateway_response, true) ?? [];
            $transaction->update([
                'gateway_response' => json_encode(array_merge($existingResponse, $data))
            ]);
            
            // ✅ Check if payment was successful selon la doc PayPlus
            if (isset($data['description']) && $data['description'] === 'completed') {
                $transaction->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now()
                ]);
                
                // Add funds to user's wallet
                $addResult = $this->walletService->addFunds(
                    $transaction->user_id,
                    $transaction->amount,
                    $transaction->id,
                    'Dépôt PayPlus - ' . $transaction->reference
                );
                
                Log::info('✅ Deposit completed', [
                    'transaction_id' => $transactionId,
                    'amount' => $transaction->amount,
                    'wallet_updated' => $addResult
                ]);
                
                return true;
                
            } else {
                // Payment failed
                $transaction->update([
                    'status' => 'FAILED'
                ]);
                
                Log::warning('❌ Deposit failed', [
                    'transaction_id' => $transactionId,
                    'description' => $data['description'] ?? 'N/A'
                ]);
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Deposit callback error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            
            return false;
        }
    }
    
    /**
     * Process PayPlus callback for withdrawals
     */
    public function processWithdrawalCallback($transactionId, $data)
    {
        try {
            Log::info('Processing withdrawal callback', [
                'transaction_id' => $transactionId,
                'data' => $data
            ]);
            
            $transaction = PaymentTransaction::find($transactionId);
            
            if (!$transaction) {
                Log::error('Withdrawal callback: Transaction not found', [
                    'transaction_id' => $transactionId
                ]);
                return false;
            }
            
            // Update transaction with callback data
            $existingResponse = json_decode($transaction->gateway_response, true) ?? [];
            $transaction->update([
                'gateway_response' => json_encode(array_merge($existingResponse, $data))
            ]);
            
            // ✅ Check if withdrawal was successful selon la doc PayPlus
            if (isset($data['description']) && $data['description'] === 'completed') {
                $transaction->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now()
                ]);
                
                Log::info('✅ Withdrawal completed', [
                    'transaction_id' => $transactionId,
                    'amount' => abs($transaction->amount)
                ]);
                
                // Withdrawal successful - funds already deducted
                return true;
                
            } else {
                // Withdrawal failed - refund the wallet
                $transaction->update([
                    'status' => 'FAILED'
                ]);
                
                Log::warning('❌ Withdrawal failed - Refunding wallet', [
                    'transaction_id' => $transactionId,
                    'description' => $data['description'] ?? 'N/A'
                ]);
                
                // Refund the amount back to wallet
                $refundResult = $this->walletService->addFunds(
                    $transaction->user_id,
                    abs($transaction->amount),
                    $transaction->id,
                    'Remboursement retrait échoué - ' . $transaction->reference
                );
                
                Log::info('Wallet refunded', [
                    'transaction_id' => $transactionId,
                    'refund_result' => $refundResult
                ]);
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Withdrawal callback error', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}