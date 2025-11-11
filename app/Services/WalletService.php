<?php
// File: app/Services/WalletService.php (Version sans plans)

namespace App\Services;

use App\Models\Wallet;
use App\Models\Transaction;
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WalletService
{
    use Utils;
    
    /**
     * Get the wallet balance for a user
     */
    public function getBalance($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            $wallet = $this->createWallet($userId);
        }
        
        return $wallet->balance;
    }
    
    /**
     * Get wallet ID for a user
     */
    public function getWalletId($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            $wallet = $this->createWallet($userId);
        }
        
        return $wallet->id;
    }
    
    /**
     * Get wallet for a user
     */
    public function getWallet($userId)
    {
        $wallet = Wallet::where('user_id', $userId)->first();
        
        if (!$wallet) {
            $wallet = $this->createWallet($userId);
        }
        
        return $wallet;
    }
    
    /**
     * Create a new wallet for a user
     */
    public function createWallet($userId)
    {
        return Wallet::create([
            'id' => $this->getId(),
            'user_id' => $userId,
            'balance' => 0,
            'currency' => 'XOF',
            'status' => 'ACTIVE'
        ]);
    }
    
    /**
     * Get transactions for a user
     */
    public function getTransactions($userId, $limit = 10)
    {
        return Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get paginated transactions for a user
     */
    public function getPaginatedTransactions($userId, $perPage = 20)
    {
        return Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
    
    /**
     * Get transaction statistics for a user
     */
    public function getTransactionStats($userId)
    {
        $stats = [
            'total_transactions' => 0,
            'total_credits' => 0,
            'total_debits' => 0,
            'this_month_credits' => 0,
            'this_month_debits' => 0,
            'last_transaction_date' => null
        ];
        
        // Total transactions
        $stats['total_transactions'] = Transaction::where('user_id', $userId)->count();
        
        // Total credits and debits
        $stats['total_credits'] = Transaction::where('user_id', $userId)
            ->where('type', 'Crédit')
            ->sum('amount');
            
        $stats['total_debits'] = Transaction::where('user_id', $userId)
            ->where('type', 'Débit')
            ->sum('amount');
        
        // This month's transactions
        $thisMonth = Carbon::now()->startOfMonth();
        $stats['this_month_credits'] = Transaction::where('user_id', $userId)
            ->where('type', 'Crédit')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');
            
        $stats['this_month_debits'] = Transaction::where('user_id', $userId)
            ->where('type', 'Débit')
            ->where('created_at', '>=', $thisMonth)
            ->sum('amount');
        
        // Last transaction date
        $lastTransaction = Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();
            
        if ($lastTransaction) {
            $stats['last_transaction_date'] = $lastTransaction->created_at;
        }
        
        return $stats;
    }
    
    /**
     * Add funds to user's wallet
     */
    public function addFunds($userId, $amount, $transactionId, $description = 'Rechargement de compte')
    {
        try {
            DB::beginTransaction();
            
            $wallet = $this->getWallet($userId);
            $wallet->balance += $amount;
            $wallet->save();
            
            Transaction::create([
                'id' => $this->getId(),
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'Crédit',
                'status' => 'COMPLETED',
                'description' => $description,
                'transaction_id' => $transactionId,
                'receipt_url' => null,
                'related_id' => $transactionId
            ]);
            
            DB::commit();
            
            Log::info('Funds added to wallet', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $wallet->balance,
                'description' => $description
            ]);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add funds to wallet: ' . $e->getMessage(), [
                'user_id' => $userId,
                'amount' => $amount
            ]);
            return false;
        }
    }
    
    /**
     * Deduct funds from user's wallet
     */
    public function deductFunds($userId, $amount, $description = 'Paiement', $relatedId = null)
    {
        try {
            DB::beginTransaction();
            
            $wallet = $this->getWallet($userId);
            
            if ($wallet->balance < $amount) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Solde insuffisant',
                    'available' => $wallet->balance,
                    'required' => $amount
                ];
            }
            
            $wallet->balance -= $amount;
            $wallet->save();
            
            $transaction = Transaction::create([
                'id' => $this->getId(),
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'Débit',
                'status' => 'COMPLETED',
                'description' => $description,
                'transaction_id' => $this->getId(),
                'receipt_url' => null,
                'related_id' => $relatedId
            ]);
            
            DB::commit();
            
            Log::info('Funds deducted from wallet', [
                'user_id' => $userId,
                'amount' => $amount,
                'new_balance' => $wallet->balance,
                'description' => $description
            ]);
            
            return [
                'success' => true,
                'message' => 'Paiement effectué avec succès',
                'transaction_id' => $transaction->id,
                'new_balance' => $wallet->balance
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to deduct funds from wallet: ' . $e->getMessage(), [
                'user_id' => $userId,
                'amount' => $amount
            ]);
            return [
                'success' => false,
                'message' => 'Une erreur est survenue: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check if user has sufficient balance
     */
    public function hasSufficientBalance($userId, $amount)
    {
        return $this->getBalance($userId) >= $amount;
    }
    
    /**
     * Get wallet summary for a user (sans plans)
     */
    public function getWalletSummary($userId)
    {
        $wallet = $this->getWallet($userId);
        $stats = $this->getTransactionStats($userId);
        
        return [
            'wallet' => [
                'id' => $wallet->id,
                'balance' => $wallet->balance,
                'currency' => $wallet->currency,
                'status' => $wallet->status,
                'created_at' => $wallet->created_at,
                'updated_at' => $wallet->updated_at
            ],
            'statistics' => $stats
        ];
    }
    
    /**
     * Transfer funds between users
     */
    public function transferFunds($fromUserId, $toUserId, $amount, $description = 'Transfert')
    {
        try {
            DB::beginTransaction();
            
            $deductResult = $this->deductFunds($fromUserId, $amount, $description . ' (envoyé)');
            
            if (!$deductResult['success']) {
                DB::rollBack();
                return $deductResult;
            }
            
            $addResult = $this->addFunds($toUserId, $amount, $deductResult['transaction_id'], $description . ' (reçu)');
            
            if (!$addResult) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Erreur lors du transfert'
                ];
            }
            
            DB::commit();
            
            Log::info('Funds transferred', [
                'from_user' => $fromUserId,
                'to_user' => $toUserId,
                'amount' => $amount,
                'description' => $description
            ]);
            
            return [
                'success' => true,
                'message' => 'Transfert effectué avec succès',
                'transaction_id' => $deductResult['transaction_id']
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer failed: ' . $e->getMessage(), [
                'from_user' => $fromUserId,
                'to_user' => $toUserId,
                'amount' => $amount
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors du transfert'
            ];
        }
    }
    
    /**
     * Freeze/unfreeze a wallet
     */
    public function setWalletStatus($userId, $freeze, $reason = '')
    {
        try {
            $wallet = $this->getWallet($userId);
            
            $newStatus = $freeze ? 'FROZEN' : 'ACTIVE';
            $wallet->status = $newStatus;
            $wallet->save();
            
            Transaction::create([
                'id' => $this->getId(),
                'user_id' => $userId,
                'wallet_id' => $wallet->id,
                'amount' => 0,
                'type' => 'ADMIN_ACTION',
                'status' => 'COMPLETED',
                'description' => "Portefeuille " . ($freeze ? 'gelé' : 'dégelé') . ": " . $reason,
                'transaction_id' => $this->getId(),
                'receipt_url' => null
            ]);
            
            return [
                'success' => true,
                'message' => 'Statut du portefeuille mis à jour',
                'new_status' => $newStatus
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to update wallet status: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut'
            ];
        }
    }
}