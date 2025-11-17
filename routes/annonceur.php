<?php

// File: routes/announcer.php (Updated with wallet routes)

use App\Http\Controllers\Web\CampaignController;
use App\Http\Controllers\Web\InfluencerController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\WalletController;
use App\Http\Controllers\Web\MessageController;
use App\Http\Controllers\Web\SettingsController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Support\Facades\Route;

// Announcer routes (all protected by auth middleware and ANNONCEUR profile check)
Route::middleware(['auth'])->prefix('admin/announcer')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'announcerDashboard'])
        ->name('announcer.dashboard');

    // Campaigns
    Route::get('/campaigns', [CampaignController::class, 'index'])
        ->name('announcer.campaigns.index');
    Route::get('/campaigns/create', [CampaignController::class, 'create'])
        ->name('announcer.campaigns.create');
    Route::post('/campaigns', [CampaignController::class, 'store'])
        ->name('announcer.campaigns.store');
    Route::get('/campaigns/{id}', [CampaignController::class, 'show'])
        ->name('announcer.campaigns.show');
    Route::get('/campaigns/{id}/edit', [CampaignController::class, 'edit'])
        ->name('announcer.campaigns.edit');
    Route::put('/campaigns/{id}', [CampaignController::class, 'update'])
        ->name('announcer.campaigns.update');
    Route::delete('/campaigns/{id}', [CampaignController::class, 'delete'])
        ->name('announcer.campaigns.delete');

    // Influencers
    Route::get('/influencers', [InfluencerController::class, 'index'])
        ->name('announcer.influencers.index');
    Route::get('/influencers/{id}', [InfluencerController::class, 'show'])
        ->name('announcer.influencers.show');

    // Reports & Analytics
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('announcer.reports.index');

    // Wallet Management (Enhanced)
    Route::prefix('wallet')->group(function () {
        // Main wallet page
        Route::get('/', [WalletController::class, 'index'])
            ->name('announcer.wallet');

        // Add funds (deposit)
        Route::post('/add-funds', [WalletController::class, 'addFunds'])
            ->name('announcer.wallet.add-funds');

        // Payment return handler
        Route::get('/payment-return', [WalletController::class, 'handlePaymentReturn'])
            ->name('announcer.wallet.payment-return');

        // Transaction history (full page)
        Route::get('/history', [WalletController::class, 'transactionHistory'])
            ->name('announcer.wallet.history');

        // API endpoints for AJAX calls
        Route::get('/stats', [WalletController::class, 'getWalletStats'])
            ->name('announcer.wallet.stats');
    });

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])
        ->name('announcer.messages.index');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('announcer.settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])
        ->name('announcer.settings.update');
});
