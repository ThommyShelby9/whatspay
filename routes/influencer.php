<?php

// File: routes/influencer.php (Updated with earnings routes)

use App\Http\Controllers\Web\Influencer\DashboardController;
use App\Http\Controllers\Web\Influencer\CampaignController;
use App\Http\Controllers\Web\Influencer\PerformanceController;
use App\Http\Controllers\Web\Influencer\EarningController;
use App\Http\Controllers\Web\Influencer\ProfileController;
use App\Http\Controllers\Web\Influencer\MessageController;
use App\Http\Controllers\Web\Influencer\SettingsController;
use App\Http\Controllers\Web\Influencer\WhatsAppController;
use Illuminate\Support\Facades\Route;

// Influencer routes (all protected by auth middleware and DIFFUSEUR profile check)
Route::middleware(['auth'])->prefix('admin/influencer')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('influencer.dashboard');
    
    // Campaigns
    Route::get('/agent/campaigns/available', [CampaignController::class, 'available'])
        ->name('influencer.campaigns.available');
    Route::get('/agent/campaigns/assigned', [CampaignController::class, 'assigned'])
        ->name('influencer.campaigns.assigned');
    Route::get('/agent/campaigns/{id}', [CampaignController::class, 'show'])
        ->name('influencer.campaigns.show');
    Route::get('/agent/campaigns/{id}/submit', [CampaignController::class, 'submit'])
        ->name('influencer.campaigns.submit');
    Route::post('/agent/campaigns/{id}/submit', [CampaignController::class, 'storeSubmission'])
        ->name('influencer.campaigns.storeSubmission');
    
    // Performance
    Route::get('/agent/performance', [PerformanceController::class, 'index'])
        ->name('influencer.performance');
    
    // Earnings Management (Enhanced)
    Route::prefix('agent/earnings')->group(function () {
        // Main earnings page
        Route::get('/', [EarningController::class, 'index'])
            ->name('influencer.earnings');
        
        // Withdrawal request
        Route::post('/withdraw', [EarningController::class, 'requestWithdrawal'])
            ->name('influencer.earnings.withdraw');
        
        // Export earnings data
        Route::get('/export', [EarningController::class, 'exportEarnings'])
            ->name('influencer.earnings.export');
        
        // API endpoints for AJAX calls
        Route::get('/chart-data', [EarningController::class, 'apiChartData'])
            ->name('influencer.earnings.chart-data');
    });
    
    // Profile
    Route::get('/agent/profile', [ProfileController::class, 'index'])
        ->name('influencer.profile');
    Route::put('/agent/profile', [ProfileController::class, 'update'])
        ->name('influencer.profile.update');
    
    // Messages
    Route::get('/agent/messages', [MessageController::class, 'index'])
        ->name('influencer.messages');
    
    // Settings
    Route::get('/agent/settings', [SettingsController::class, 'index'])
        ->name('influencer.settings');
    Route::put('/agent/settings', [SettingsController::class, 'update'])
        ->name('influencer.settings.update');
    
    // WhatsApp Configuration
    Route::prefix('whatsapp')->group(function () {
        Route::get('/', [WhatsAppController::class, 'index'])
            ->name('influencer.whatsapp');
        Route::post('/add', [WhatsAppController::class, 'addPhone'])
            ->name('influencer.whatsapp.add');
        Route::post('/verify', [WhatsAppController::class, 'verifyPhone'])
            ->name('influencer.whatsapp.verify');
        Route::post('/resend', [WhatsAppController::class, 'resendCode'])
            ->name('influencer.whatsapp.resend');
        Route::delete('/delete/{id}', [WhatsAppController::class, 'deletePhone'])
            ->name('influencer.whatsapp.delete');
    });
});