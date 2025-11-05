<?php
// File: app/Services/PaymentService.php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use App\Traits\Utils;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentService
{
    use Utils;
    
    protected $walletService;
    
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }
    
    /**
     * Get available payment methods
     *
     * @return \Illuminate\Database\Eloquent\Collection Payment methods
     */
    public function getAvailablePaymentMethods()
    {
        return PaymentMethod::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Initiate a payment process
     *
     * @param string $userId ID of the user
     * @param string $paymentMethodId ID or code of the payment method
     * @param float $amount Amount to pay
     * @return array Result including success status, message, and possibly a redirect URL
     */
    public function initiatePayment($userId, $paymentMethodId, $amount)
    {
        // Find the payment method
        $paymentMethod = PaymentMethod::where('id', $paymentMethodId)
            ->orWhere('code', $paymentMethodId)
            ->first();
            
        if (!$paymentMethod) {
            return [
                'success' => false,
                'message' => 'Méthode de paiement non disponible'
            ];
        }
        
        // Create a payment transaction record
        $transactionId = $this->getId();
        $paymentTransaction = PaymentTransaction::create([
            'id' => $transactionId,
            'user_id' => $userId,
            'payment_method_id' => $paymentMethod->id,
            'amount' => $amount,
            'currency' => 'XOF', // FCFA
            'status' => 'PENDING',
            'reference' => 'PAY-' . strtoupper(substr(uniqid(), 0, 8)),
            'expires_at' => Carbon::now()->addHour(), // Transaction expires in 1 hour
            'callback_url' => route('payment.callback', ['transaction' => $transactionId]),
            'payload' => json_encode([
                'user_id' => $userId,
                'amount' => $amount,
                'payment_method' => $paymentMethod->code
            ])
        ]);
        
        // Based on the payment method, redirect to the appropriate payment gateway
        switch ($paymentMethod->code) {
            case 'card':
                return $this->processCardPayment($paymentTransaction);
                
            case 'mobile_money':
                return $this->processMobileMoneyPayment($paymentTransaction);
                
            case 'bank':
                return $this->processBankTransferPayment($paymentTransaction);
                
            default:
                return [
                    'success' => false,
                    'message' => 'Méthode de paiement non prise en charge'
                ];
        }
    }
    
    /**
     * Process a card payment
     *
     * @param \App\Models\PaymentTransaction $transaction Payment transaction
     * @return array Result with redirect URL
     */
    protected function processCardPayment($transaction)
    {
        // In a real implementation, this would connect to a payment gateway
        // For demo purposes, we'll simulate a successful payment
        
        // Generate payment gateway URL
        $gatewayUrl = route('payment.gateway', [
            'transaction' => $transaction->id,
            'method' => 'card'
        ]);
        
        return [
            'success' => true,
            'message' => 'Redirection vers la passerelle de paiement',
            'redirect_url' => $gatewayUrl
        ];
    }
    
    /**
     * Process a mobile money payment
     *
     * @param \App\Models\PaymentTransaction $transaction Payment transaction
     * @return array Result with redirect URL or instructions
     */
    protected function processMobileMoneyPayment($transaction)
    {
        // Generate payment gateway URL
        $gatewayUrl = route('payment.gateway', [
            'transaction' => $transaction->id,
            'method' => 'mobile_money'
        ]);
        
        return [
            'success' => true,
            'message' => 'Redirection vers Mobile Money',
            'redirect_url' => $gatewayUrl
        ];
    }
    
    /**
     * Process a bank transfer payment
     *
     * @param \App\Models\PaymentTransaction $transaction Payment transaction
     * @return array Result with bank details
     */
    protected function processBankTransferPayment($transaction)
    {
        // For bank transfers, we'll provide bank details instead of a redirect
        
        // Update transaction with bank transfer details
        $transaction->update([
            'payload' => json_encode([
                'bank_name' => 'Banque Atlantique',
                'account_number' => 'WP-' . rand(10000, 99999),
                'account_name' => 'WhatsPAY SAS',
                'reference' => $transaction->reference
            ])
        ]);
        
        // Generate payment instructions page URL
        $instructionsUrl = route('payment.instructions', [
            'transaction' => $transaction->id,
            'method' => 'bank'
        ]);
        
        return [
            'success' => true,
            'message' => 'Veuillez effectuer un virement bancaire avec les détails fournis',
            'redirect_url' => $instructionsUrl
        ];
    }
    
    /**
     * Process a payment callback (webhook)
     *
     * @param string $transactionId ID of the transaction
     * @param array $data Payment gateway response data
     * @return bool Success status
     */
    public function processPaymentCallback($transactionId, $data)
    {
        try {
            // Find the transaction
            $transaction = PaymentTransaction::find($transactionId);
            
            if (!$transaction) {
                Log::error('Payment callback: Transaction not found', [
                    'transaction_id' => $transactionId,
                    'data' => $data
                ]);
                return false;
            }
            
            // Check if payment was successful
            if (isset($data['status']) && $data['status'] === 'success') {
                // Update transaction status
                $transaction->update([
                    'status' => 'COMPLETED',
                    'completed_at' => Carbon::now(),
                    'gateway_response' => json_encode($data)
                ]);
                
                // Add funds to user's wallet
                $this->walletService->addFunds(
                    $transaction->user_id,
                    $transaction->amount,
                    $transaction->id,
                    'Rechargement par ' . $transaction->paymentMethod->name
                );
                
                return true;
            } else {
                // Payment failed
                $transaction->update([
                    'status' => 'FAILED',
                    'gateway_response' => json_encode($data)
                ]);
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Payment callback error: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'data' => $data,
                'exception' => $e
            ]);
            
            return false;
        }
    }
    
    /**
     * Check payment status
     *
     * @param string $transactionId ID of the transaction
     * @return array Status and details
     */
    public function checkPaymentStatus($transactionId)
    {
        $transaction = PaymentTransaction::find($transactionId);
        
        if (!$transaction) {
            return [
                'success' => false,
                'message' => 'Transaction non trouvée'
            ];
        }
        
        return [
            'success' => true,
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'payment_method' => $transaction->paymentMethod->name,
            'created_at' => $transaction->created_at,
            'completed_at' => $transaction->completed_at
        ];
    }
}