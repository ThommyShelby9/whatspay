<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Models\Wallet;
use App\Traits\Utils;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
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
        $this->payPlusBaseUrl = config('payplus.base_url');
        $this->payPlusApiKey = config('payplus.api_key', '57DD7H4RBP8WVAM3D');
        $this->payPlusApiToken = config('payplus.api_token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZF9hcHAiOiI0NjgyIiwiaWRfYWJvbm5lIjoxMDc4MCwiZGF0ZWNyZWF0aW9uX2FwcCI6IjIwMjUtMTEtMDEgMDI6MTU6MTIifQ.aOirgkjSysUBnUUAQG6m9eJpZu0WAz1OInYbYAqX_rY');
    }
    
    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods()
    {
        return PaymentMethod::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Initiate deposit for announcer
     *
     * @param string $userId ID of the user
     * @param float $amount Amount to deposit
     * @param string $customerPhone Customer phone number
     * @param bool $useRedirect Whether to use redirect flow or straight flow
     * @return array Result including success status, message, and redirect URL
     */
    public function initiateDeposit($userId, $amount, $customerPhone, $useRedirect = true)
    {
        try {
            // Validation
            if ($amount < 1000) { // Minimum 1000 XOF
                return [
                    'success' => false,
                    'message' => 'Le montant minimum de dépôt est de 1000 FCFA'
                ];
            }
            
            // Create payment transaction record
            $transactionId = $this->getId();
            $externalId = 'DEP-' . time() . '-' . substr($transactionId, 0, 8);
            
            $paymentTransaction = PaymentTransaction::create([
                'id' => $transactionId,
                'user_id' => $userId,
                'payment_method_id' => null, // Will be updated based on user choice
                'amount' => $amount,
                'currency' => 'XOF',
                'status' => 'PENDING',
                'reference' => $externalId,
                'expires_at' => Carbon::now()->addHour(),
                'callback_url' => route('payment.callback', ['transaction' => $transactionId]),
                'payload' => json_encode([
                    'type' => 'deposit',
                    'user_id' => $userId,
                    'amount' => $amount,
                    'phone' => $customerPhone
                ])
            ]);
            
            // Prepare PayPlus payload
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
                        'customer' => $customerPhone,
                        'customer_firstname' => '',
                        'customer_lastname' => '',
                        'customer_email' => '',
                        'external_id' => $externalId,
                        'otp' => ''
                    ],
                    'store' => [
                        'name' => 'WhatsPAY',
                        'website_url' => config('app.url')
                    ],
                    'actions' => [
                        'cancel_url' => route('announcer.wallet') . '?status=cancelled',
                        'return_url' => route('announcer.wallet') . '?status=success',
                        'callback_url' => route('payment.callback', ['transaction' => $transactionId]),
                        'callback_url_method' => 'post_json'
                    ],
                    'custom_data' => [
                        'transaction_id' => $transactionId,
                        'hash' => hash('sha256', $transactionId . $amount . $userId)
                    ]
                ]
            ];
            
            // Choose endpoint based on flow type
            $endpoint = $useRedirect ? 
                '/pay/v01/redirect/checkout-invoice/create' : 
                '/pay/v01/straight/checkout-invoice/create';
            
            // Call PayPlus API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->payPlusApiToken,
                'Apikey' => $this->payPlusApiKey,
                'Content-Type' => 'application/json'
            ])->post($this->payPlusBaseUrl . $endpoint, $payload);
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                if ($responseData['response_code'] === '00') {
                    // Update transaction with PayPlus token
                    $paymentTransaction->update([
                        'gateway_response' => json_encode($responseData)
                    ]);
                    
                    return [
                        'success' => true,
                        'message' => 'Redirection vers la passerelle de paiement',
                        'redirect_url' => $responseData['response_text'],
                        'transaction_id' => $transactionId,
                        'token' => $responseData['token']
                    ];
                } else {
                    // Payment initiation failed
                    $paymentTransaction->update([
                        'status' => 'FAILED',
                        'gateway_response' => json_encode($responseData)
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $responseData['description'] ?? 'Erreur lors de l\'initialisation du paiement'
                    ];
                }
            } else {
                throw new \Exception('Erreur de communication avec PayPlus: ' . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error('PayPlus deposit error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ];
        }
    }
    
    /**
     * Initiate withdrawal for influencer
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
            
            $paymentTransaction = PaymentTransaction::create([
                'id' => $transactionId,
                'user_id' => $userId,
                'amount' => -$amount, // Negative for withdrawal
                'currency' => 'XOF',
                'status' => 'PENDING',
                'reference' => $externalId,
                'expires_at' => Carbon::now()->addHour(),
                'callback_url' => route('payment.callback.withdrawal', ['transaction' => $transactionId]),
                'payload' => json_encode([
                    'type' => 'withdrawal',
                    'user_id' => $userId,
                    'amount' => $amount,
                    'phone' => $customerPhone,
                    'use_internal_wallet' => $useInternalWallet
                ])
            ]);
            
            // Prepare PayPlus payload
            $payload = [
                'commande' => [
                    'amount' => $amount,
                    'customer' => $customerPhone,
                    'custom_data' => [
                        'transaction_id' => $transactionId,
                        'hash' => hash('sha256', $transactionId . $amount . $userId)
                    ],
                    'callback_url' => route('payment.callback.withdrawal', ['transaction' => $transactionId]),
                    'callback_url_method' => 'post_json',
                    'external_id' => $externalId,
                    'network' => ''
                ]
            ];
            
            // Add top_up_wallet for internal wallet
            if ($useInternalWallet) {
                $payload['commande']['top_up_wallet'] = 1;
            }
            
            // Choose endpoint
            $endpoint = $useInternalWallet ? 
                '/pay/v01/withdrawal/create' : 
                '/pay/v01/straight/payout';
            
            // Call PayPlus API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->payPlusApiToken,
                'Apikey' => $this->payPlusApiKey,
                'Content-Type' => 'application/json'
            ])->post($this->payPlusBaseUrl . $endpoint, $payload);
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                if ($responseData['response_code'] === '00') {
                    // Deduct from wallet immediately (will be reversed if withdrawal fails)
                    $deductResult = $this->walletService->deductFunds(
                        $userId,
                        $amount,
                        'Retrait vers ' . $customerPhone
                    );
                    
                    if (!$deductResult['success']) {
                        return $deductResult;
                    }
                    
                    // Update transaction
                    $paymentTransaction->update([
                        'status' => 'PROCESSING',
                        'gateway_response' => json_encode($responseData)
                    ]);
                    
                    return [
                        'success' => true,
                        'message' => 'Demande de retrait initiée. Vous recevrez une confirmation sous peu.',
                        'transaction_id' => $transactionId,
                        'token' => $responseData['token']
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $responseData['description'] ?? 'Erreur lors de l\'initialisation du retrait'
                    ];
                }
            } else {
                throw new \Exception('Erreur de communication avec PayPlus: ' . $response->status());
            }
            
        } catch (\Exception $e) {
            Log::error('PayPlus withdrawal error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ];
        }
    }
    
    /**
     * Process PayPlus callback for deposits
     */
    public function processDepositCallback($transactionId, $data)
    {
        try {
            $transaction = PaymentTransaction::find($transactionId);
            
            if (!$transaction) {
                Log::error('Deposit callback: Transaction not found', [
                    'transaction_id' => $transactionId,
                    'data' => $data
                ]);
                return false;
            }
            
            // Update transaction with callback data
            $transaction->update([
                'gateway_response' => json_encode(array_merge(
                    json_decode($transaction->gateway_response, true) ?? [],
                    $data
                ))
            ]);
            
            // Check if payment was successful
            if (isset($data['description']) && $data['description'] === 'completed') {
                $transaction->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now()
                ]);
                
                // Add funds to user's wallet
                $this->walletService->addFunds(
                    $transaction->user_id,
                    $transaction->amount,
                    $transaction->id,
                    'Dépôt PayPlus - ' . $transaction->reference
                );
                
                return true;
            } else {
                // Payment failed
                $transaction->update([
                    'status' => 'FAILED'
                ]);
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Deposit callback error: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
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
            $transaction = PaymentTransaction::find($transactionId);
            
            if (!$transaction) {
                Log::error('Withdrawal callback: Transaction not found', [
                    'transaction_id' => $transactionId
                ]);
                return false;
            }
            
            // Update transaction
            $transaction->update([
                'gateway_response' => json_encode(array_merge(
                    json_decode($transaction->gateway_response, true) ?? [],
                    $data
                ))
            ]);
            
            if (isset($data['description']) && $data['description'] === 'completed') {
                $transaction->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now()
                ]);
                
                // Withdrawal successful - funds already deducted
                return true;
            } else {
                // Withdrawal failed - refund the wallet
                $transaction->update([
                    'status' => 'FAILED'
                ]);
                
                // Refund the amount back to wallet
                $this->walletService->addFunds(
                    $transaction->user_id,
                    abs($transaction->amount),
                    $transaction->id,
                    'Remboursement retrait échoué - ' . $transaction->reference
                );
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Withdrawal callback error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check transaction status with PayPlus
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
            
            // Determine endpoint based on transaction type
            $payload = json_decode($transaction->payload, true);
            $isWithdrawal = $payload['type'] === 'withdrawal';
            
            $endpoint = $isWithdrawal ? 
                '/pay/v01/withdrawal/confirm' : 
                '/pay/v01/redirect/checkout-invoice/confirm';
                
            $paramName = $isWithdrawal ? 'withdrawalToken' : 'invoiceToken';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->payPlusApiToken,
                'Apikey' => $this->payPlusApiKey,
                'Content-Type' => 'application/json'
            ])->get($this->payPlusBaseUrl . $endpoint, [
                $paramName => $payPlusToken
            ]);
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                return [
                    'success' => true,
                    'status' => $responseData['description'] ?? 'unknown',
                    'payplus_response' => $responseData,
                    'local_status' => $transaction->status
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la vérification du statut'
                ];
            }
            
        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ];
        }
    }
}