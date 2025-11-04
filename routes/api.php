<?php

use App\Http\Controllers\Api\MediaApiController;
use App\Http\Controllers\Api\TaskApiController;
use App\Http\Controllers\Api\AssignmentApiController;
use App\Http\Controllers\Api\TrackingApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\WhatsAppApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route sanctum par défaut
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Routes publiques
Route::post('/upload', [MediaApiController::class, 'upload'])->name('api.upload');
Route::post('/whatsappnotifier', [WhatsAppApiController::class, 'whatsappnotifier'])->name('api.whatsapp_notifier');
Route::post('whatsapp/generatecode', [WhatsAppApiController::class, 'whatsappGeneratecode'])->name('api.whatsapp_generate_code');
Route::post('whatsapp/validatecode', [WhatsAppApiController::class, 'whatsappValidatecode'])->name('api.whatsapp_validate_code');

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Tracking
    Route::post('/track-click', [TrackingApiController::class, 'trackClick'])->name('api.track_click');
    Route::get('/stats/{taskId}', [TrackingApiController::class, 'getStats'])->name('api.get_stats');
    Route::get('/global-stats', [TrackingApiController::class, 'getGlobalStats'])->name('api.get_global_stats');
    
    // Media
    Route::get('/media/{id}', [MediaApiController::class, 'getMedia'])->name('api.get_media');
    Route::delete('/media/{id}', [MediaApiController::class, 'deleteMedia'])->name('api.delete_media');
    Route::get('/tasks/{taskId}/media', [MediaApiController::class, 'getTaskMedia'])->name('api.task.media');


    // Tasks
    Route::apiResource('tasks', TaskApiController::class);
    Route::post('/tasks/{id}/approve', [TaskApiController::class, 'approve'])->name('api.task.approve');
    Route::post('/tasks/{id}/reject', [TaskApiController::class, 'reject'])->name('api.task.reject');
    
    // Assignments
    Route::apiResource('assignments', AssignmentApiController::class);
    Route::post('/assignments/{id}/submit', [AssignmentApiController::class, 'submit'])->name('api.assignment.submit');
    Route::get('/tasks/{taskId}/available-agents', [AssignmentApiController::class, 'getAvailableAgents'])->name('api.task.available_agents');
    
    // Users
    Route::apiResource('users', UserApiController::class);
    Route::post('/users/{id}/toggle-status', [UserApiController::class, 'toggleStatus'])->name('api.user.toggle_status');
    Route::get('/influencers/by-category/{categoryId}', [UserApiController::class, 'getInfluencersByCategory'])->name('api.influencers_by_category');
});

// Route pour le tracking de lien public (ne nécessite pas d'auth)
Route::get('/track/{linkId}', [TrackingApiController::class, 'handleTrackingRedirect'])->name('track.redirect');