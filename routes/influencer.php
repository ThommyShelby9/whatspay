<?php
// File: routes/influencer.php

use App\Http\Controllers\Web\Influencer\DashboardController;
use App\Http\Controllers\Web\Influencer\CampaignController;
use App\Http\Controllers\Web\Influencer\PerformanceController;
use App\Http\Controllers\Web\Influencer\EarningController;
use App\Http\Controllers\Web\Influencer\ProfileController;
use App\Http\Controllers\Web\Influencer\MessageController;
use App\Http\Controllers\Web\Influencer\SettingsController;
use App\Http\Controllers\Web\Influencer\WhatsAppController;
use Illuminate\Support\Facades\Route;

// Vérifier le profil DIFFUSEUR
Route::middleware(['auth'])->prefix('admin/influencer')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('influencer.dashboard');
    
    // Campagnes
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
    
    // Performances
    Route::get('/agent/performance', [PerformanceController::class, 'index'])
        ->name('influencer.performance');
    
    // Gains
    Route::get('/agent/earnings', [EarningController::class, 'index'])
        ->name('influencer.earnings');
    
    // Profil
    Route::get('/agent/profile', [ProfileController::class, 'index'])
        ->name('influencer.profile');
    Route::put('/agent/profile', [ProfileController::class, 'update'])
        ->name('influencer.profile.update');
    
    // Messages
    Route::get('/agent/messages', [MessageController::class, 'index'])
        ->name('influencer.messages');
    
    // Paramètres
    Route::get('/agent/settings', [SettingsController::class, 'index'])
        ->name('influencer.settings');
    Route::put('/agent/settings', [SettingsController::class, 'update'])
        ->name('influencer.settings.update');
    
    // Configuration WhatsApp
    Route::get('/agent/whatsapp', [WhatsAppController::class, 'index'])
        ->name('influencer.whatsapp');

        Route::get('whatsapp', [WhatsAppController::class, 'index'])->name('influencer.whatsapp');
    Route::post('whatsapp/add', [WhatsAppController::class, 'addPhone'])->name('influencer.whatsapp.add');
    Route::post('whatsapp/verify', [WhatsAppController::class, 'verifyPhone'])->name('influencer.whatsapp.verify');
    Route::post('whatsapp/resend', [WhatsAppController::class, 'resendCode'])->name('influencer.whatsapp.resend');
    Route::delete('whatsapp/delete/{id}', [WhatsAppController::class, 'deletePhone'])->name('influencer.whatsapp.delete');
});