<?php
// File: app/Services/WalletService.php

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\Plan;
use App\Models\PlanSubscription;
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WalletService
{
    use Utils;
    
    /**
     * Get the wallet balance for a user
     *
     * @param string $userId ID of the user
     * @return float Current balance
     */
    public function getBalance($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            // Create a new wallet with zero balance if one doesn't exist
            $wallet = $this->createWallet($userId);
        }
        
        return $wallet->balance;
    }
    
    /**
     * Create a new wallet for a user
     *
     * @param string $userId ID of the user
     * @return \App\Models\Wallet The created wallet
     */
    public function createWallet($userId)
    {
        return Wallet::create([
            'id' => $this->getId(),
            'user_id' => $userId,
            'balance' => 0,
            'currency' => 'XOF', // FCFA by default
            'status' => 'ACTIVE'
        ]);
    }
    
    /**
     * Get the current plan for a user
     *
     * @param string $userId ID of the user
     * @return object|null Plan information or null if no active plan
     */
    public function getCurrentPlan($userId)
    {
        $subscription = PlanSubscription::where('user_id', $userId)
            ->where('status', 'ACTIVE')
            ->where('valid_until', '>=', Carbon::now())
            ->orderBy('valid_until', 'desc')
            ->first();
            
        if (!$subscription) {
            return null;
        }
        
        $plan = Plan::find($subscription->plan_id);
        
        if (!$plan) {
            return null;
        }
        
        // Get plan features
        $features = DB::table('plan_features')
            ->where('plan_id', $plan->id)
            ->pluck('name')
            ->toArray();
            
        $planData = (object) [
            'id' => $plan->id,
            'name' => $plan->name,
            'valid_until' => $subscription->valid_until,
            'price' => $plan->price,
            'features' => $features
        ];
        
        return $planData;
    }
    
    /**
     * Get transactions for a user
     *
     * @param string $userId ID of the user
     * @param int $limit Number of transactions to retrieve (default 10)
     * @return \Illuminate\Database\Eloquent\Collection Transactions
     */
    public function getTransactions($userId, $limit = 10)
    {
        return Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Add funds to user's wallet
     *
     * @param string $userId ID of the user
     * @param float $amount Amount to add
     * @param string $transactionId ID of the related payment transaction
     * @param string $description Description of the transaction
     * @return bool Success status
     */
    public function addFunds($userId, $amount, $transactionId, $description = 'Rechargement de compte')
    {
        try {
            DB::beginTransaction();
            
            // Get or create wallet
            $wallet = Wallet::where('user_id', $userId)->first();
            
            if (!$wallet) {
                $wallet = $this->createWallet($userId);
            }
            
            // Update wallet balance
            $wallet->balance += $amount;
            $wallet->save();
            
            // Create transaction record
            Transaction::create([
                'id' => $this->getId(),
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'Crédit',
                'status' => 'COMPLETED',
                'description' => $description,
                'transaction_id' => $transactionId,
                'receipt_url' => null // To be generated later
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
    
    /**
     * Deduct funds from user's wallet
     *
     * @param string $userId ID of the user
     * @param float $amount Amount to deduct
     * @param string $description Description of the transaction
     * @return array Result status and message
     */
    public function deductFunds($userId, $amount, $description = 'Paiement')
    {
        try {
            DB::beginTransaction();
            
            // Get wallet
            $wallet = Wallet::where('user_id', $userId)->first();
            
            if (!$wallet) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Portefeuille non trouvé'
                ];
            }
            
            // Check if there are enough funds
            if ($wallet->balance < $amount) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Solde insuffisant'
                ];
            }
            
            // Update wallet balance
            $wallet->balance -= $amount;
            $wallet->save();
            
            // Create transaction record
            $transaction = Transaction::create([
                'id' => $this->getId(),
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'Débit',
                'status' => 'COMPLETED',
                'description' => $description,
                'transaction_id' => $this->getId(), // Generate a unique transaction ID
                'receipt_url' => null // To be generated later
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Paiement effectué avec succès',
                'transaction_id' => $transaction->id
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate a receipt URL for a transaction
     *
     * @param string $transactionId ID of the transaction
     * @return string|null Receipt URL or null on failure
     */
    public function generateReceiptUrl($transactionId)
    {
        // This would normally generate a PDF receipt and store it
        // For now, we'll just return a placeholder URL
        $baseUrl = config('app.url');
        return $baseUrl . '/receipts/' . $transactionId . '.pdf';
    }
}