<?php

// File: routes/payment.php
use App\Http\Controllers\Web\PaymentCallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Payment Routes
|--------------------------------------------------------------------------
|
| Routes for handling PayPlus callbacks and payment pages
|
*/

// PayPlus callback routes (public, no authentication required)
Route::prefix('payment')->group(function () {
    // Deposit callbacks (accepter GET et POST pour compatibilité)
    Route::match(['get', 'post'], 'callback/{transaction}', [PaymentCallbackController::class, 'handleDepositCallback'])
        ->name('payment.callback');

    // Withdrawal callbacks (accepter GET et POST pour compatibilité)
    Route::match(['get', 'post'], 'callback/withdrawal/{transaction}', [PaymentCallbackController::class, 'handleWithdrawalCallback'])
        ->name('payment.callback.withdrawal');
    
    // Payment gateway simulation pages (for testing)
    Route::get('gateway/{transaction}', [PaymentCallbackController::class, 'showPaymentGateway'])
        ->name('payment.gateway');
    
    // Payment instructions page
    Route::get('instructions/{transaction}', [PaymentCallbackController::class, 'showPaymentInstructions'])
        ->name('payment.instructions');
    
    // Status check endpoint
    Route::get('status/{transaction}', [PaymentCallbackController::class, 'checkStatus'])
        ->name('payment.status');

    // ✅ NOUVEAU: Endpoint de test pour vérifier que PayPlus peut joindre le serveur
    Route::get('callback/test', function () {
        return response()->json([
            'success' => true,
            'message' => 'Callback endpoint accessible',
            'timestamp' => now()->toDateTimeString(),
            'server' => request()->server('SERVER_NAME'),
            'ip' => request()->ip()
        ]);
    })->name('payment.callback.test');
});

// Protected payment routes
Route::middleware(['auth'])->prefix('payment')->group(function () {
    // Transaction history
    Route::get('history', [PaymentCallbackController::class, 'transactionHistory'])
        ->name('payment.history');
    
    // Transaction details
    Route::get('transaction/{transaction}', [PaymentCallbackController::class, 'transactionDetails'])
        ->name('payment.transaction');
});