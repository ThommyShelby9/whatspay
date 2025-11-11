<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Models\PaymentTransaction;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    use Utils;
    
    protected $paymentService;
    
    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    
    /**
     * Handle PayPlus deposit callback
     */
    public function handleDepositCallback(Request $request, $transactionId)
    {
        try {
            // Log the callback for debugging
            Log::info('PayPlus deposit callback received', [
                'transaction_id' => $transactionId,
                'data' => $request->all(),
                'headers' => $request->headers->all()
            ]);
            
            // Validate the callback data
            $callbackData = $request->all();
            
            // Process the callback
            $success = $this->paymentService->processDepositCallback($transactionId, $callbackData);
            
            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Callback processed successfully'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Callback processing failed'
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Deposit callback error: ' . $e->getMessage(), [
                'transaction_id' => $transactionId,
                'data' => $request->all()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Handle PayPlus withdrawal callback
     */
    public function handleWithdrawalCallback(Request $request, $transactionId)
    {
        try {
            Log::info('PayPlus withdrawal callback received', [
                'transaction_id' => $transactionId,
                'data' => $request->all()
            ]);
            
            $callbackData = $request->all();
            $success = $this->paymentService->processWithdrawalCallback($transactionId, $callbackData);
            
            if ($success) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Withdrawal callback processed successfully'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Withdrawal callback processing failed'
                ], 400);
            }
            
        } catch (\Exception $e) {
            Log::error('Withdrawal callback error: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }
    
    /**
     * Show payment gateway simulation page (for testing)
     */
    public function showPaymentGateway(Request $request, $transactionId)
    {
        $transaction = PaymentTransaction::find($transactionId);
        
        if (!$transaction) {
            return redirect()->route('announcer.wallet')->with([
                'type' => 'danger',
                'message' => 'Transaction non trouvée'
            ]);
        }
        
        if ($transaction->status !== 'PENDING') {
            return redirect()->route('announcer.wallet')->with([
                'type' => 'info',
                'message' => 'Cette transaction a déjà été traitée'
            ]);
        }
        
        // For testing purposes, show a simple payment gateway simulation
        return view('payment.gateway', [
            'transaction' => $transaction,
            'method' => $request->get('method', 'mobile_money'),
            'title' => 'Passerelle de paiement - WhatsPAY'
        ]);
    }
    
    /**
     * Show payment instructions (for bank transfers)
     */
    public function showPaymentInstructions(Request $request, $transactionId)
    {
        $transaction = PaymentTransaction::find($transactionId);
        
        if (!$transaction) {
            return redirect()->route('announcer.wallet')->with([
                'type' => 'danger',
                'message' => 'Transaction non trouvée'
            ]);
        }
        
        $payload = json_decode($transaction->payload, true);
        
        return view('payment.instructions', [
            'transaction' => $transaction,
            'instructions' => $payload,
            'title' => 'Instructions de paiement - WhatsPAY'
        ]);
    }
    
    /**
     * Check transaction status
     */
    public function checkStatus($transactionId)
    {
        try {
            $result = $this->paymentService->checkTransactionStatus($transactionId);
            
            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut'
            ], 500);
        }
    }
    
    /**
     * Show transaction history for authenticated user
     */
    public function transactionHistory(Request $request)
    {
        $userId = $request->session()->get('userid');
        
        $transactions = PaymentTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $viewData = [
            'transactions' => $transactions,
            'user' => $request->session()->get('user'),
            'userid' => $userId
        ];
        
        return view('payment.history', [
            'viewData' => $viewData,
            'title' => 'WhatsPAY | Historique des transactions',
            'pagetilte' => 'Historique des transactions',
            'pagecardtilte' => 'Toutes vos transactions'
        ]);
    }
    
    /**
     * Show transaction details
     */
    public function transactionDetails(Request $request, $transactionId)
    {
        $userId = $request->session()->get('userid');
        
        $transaction = PaymentTransaction::where('id', $transactionId)
            ->where('user_id', $userId)
            ->first();
        
        if (!$transaction) {
            return redirect()->route('payment.history')->with([
                'type' => 'danger',
                'message' => 'Transaction non trouvée'
            ]);
        }
        
        $viewData = [
            'transaction' => $transaction,
            'payload' => json_decode($transaction->payload, true),
            'gateway_response' => json_decode($transaction->gateway_response, true),
            'user' => $request->session()->get('user'),
            'userid' => $userId
        ];
        
        return view('payment.details', [
            'viewData' => $viewData,
            'title' => 'WhatsPAY | Détails de transaction',
            'pagetilte' => 'Détails de transaction',
            'pagecardtilte' => 'Transaction #' . substr($transaction->reference, 0, 8)
        ]);
    }
    
    /**
     * Simulate payment success (for testing only)
     */
    public function simulatePaymentSuccess(Request $request, $transactionId)
    {
        if (!app()->environment(['local', 'testing'])) {
            abort(404);
        }
        
        $transaction = PaymentTransaction::find($transactionId);
        
        if (!$transaction || $transaction->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non valide'
            ]);
        }
        
        // Simulate successful callback
        $callbackData = [
            'response_code' => '00',
            'description' => 'completed',
            'transaction_id' => $transaction->reference,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency
        ];
        
        $success = $this->paymentService->processDepositCallback($transactionId, $callbackData);
        
        return response()->json([
            'success' => $success,
            'message' => $success ? 'Paiement simulé avec succès' : 'Erreur lors de la simulation'
        ]);
    }
    
    /**
     * Simulate payment failure (for testing only)
     */
    public function simulatePaymentFailure(Request $request, $transactionId)
    {
        if (!app()->environment(['local', 'testing'])) {
            abort(404);
        }
        
        $transaction = PaymentTransaction::find($transactionId);
        
        if (!$transaction || $transaction->status !== 'PENDING') {
            return response()->json([
                'success' => false,
                'message' => 'Transaction non valide'
            ]);
        }
        
        // Simulate failed callback
        $callbackData = [
            'response_code' => '01',
            'description' => 'failed',
            'transaction_id' => $transaction->reference,
            'error_message' => 'Paiement échoué - Test'
        ];
        
        $this->paymentService->processDepositCallback($transactionId, $callbackData);
        
        return response()->json([
            'success' => true,
            'message' => 'Échec de paiement simulé'
        ]);
    }
}