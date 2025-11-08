<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WhatsAppController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

// Ajoutez cet alias
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Routes publiques
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/page/{page}', [PageController::class, 'page'])->name('page');
Route::get('/comingsoon', [PageController::class, 'comingsoon'])->name('comingsoon');

// Anciennes routes de test WhatsApp (à supprimer en production si non utilisées)
Route::get('/sendmessage/{recipient}', [WhatsAppController::class, 'sendMessage'])->name('send_message');
Route::get('/sendmessage2/{recipient}/{message?}', [WhatsAppController::class, 'sendMessage2'])->name('send_message2');

// Routes d'administration
// Routes d'administration
Route::group(['prefix' => 'admin'], function () {
    // Auth - non authentifié
    Route::get('/', [AuthController::class, 'loginGet'])->name('admin.index');
    Route::post('/', [AuthController::class, 'loginPost']);
    
    Route::get('/login', [AuthController::class, 'loginGet'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('admin.login.post');
    // Supprimez cette ligne dupliquée ou choisissez un seul nom de route
    // Route::post('/login', [AuthController::class, 'loginPost'])->name('login');

    // Routes d'inscription - maintenant correctement indentées
    Route::get('/registration', [AuthController::class, 'registrationHomeGet'])->name('admin.register');
    Route::get('/registration/diffuseur', [AuthController::class, 'registrationDiffuseurGet'])->name('admin.register.diffuseur');
    Route::get('/registration/annonceur', [AuthController::class, 'registrationAnnonceurGet'])->name('admin.register.annonceur');
    Route::post('/registration/diffuseur', [AuthController::class, 'registrationDiffuseurPost'])->name('admin.register.diffuseur.post');
    Route::post('/registration/annonceur', [AuthController::class, 'registrationAnnonceurPost'])->name('admin.register.annonceur.post');
    Route::post('/registration', [AuthController::class, 'registrationPost'])->name('admin.register.post');
    
    Route::get('/verify-account', [AuthController::class, 'verifyAccountGet'])->name('admin.verify_account');
    // ... reste de vos routes
    Route::post('/verify-account', [AuthController::class, 'verifyAccountPost'])->name('admin.verify_account.post');
    
    Route::get('/forgotten_password', [AuthController::class, 'forgotten_passwordPageGet'])->name('admin.forgotten_password');
    Route::post('/forgotten_password', [AuthController::class, 'forgotten_passwordPagePost'])->name('admin.forgotten_password.post');
    
    Route::get('/password_recovery/{token}', [AuthController::class, 'reset_passwordGet'])->name('admin.password_recovery');
    Route::post('/password_recovery/{token}', [AuthController::class, 'reset_passwordPost'])->name('admin.password_recovery.post');
    
    Route::get('/twofa_auth/{token}', [AuthController::class, 'twofa_authGet'])->name('admin.twofa_auth');
    Route::post('/twofa_auth/{token}', [AuthController::class, 'twofa_authPost'])->name('admin.twofa_auth.post');

    // Routes protégées (nécessitent authentification)
    Route::middleware(['auth'])->group(function () {
        Route::get('/logout', [AuthController::class, 'logout'])->name('admin.logout');
        Route::get('/dashboard', [DashboardController::class, 'dashboardGet'])->name('admin.dashboard');
        
        // Profil utilisateur
        Route::get('/myprofile', [UserController::class, 'profile'])->name('admin.myprofile');
        Route::post('/myprofile', [UserController::class, 'updateProfile'])->name('admin.myprofile.update');
        
        // Tâches
        Route::get('/tasks', [TaskController::class, 'tasksGet'])->name('admin.tasks');
        Route::get('/task/{id}', [TaskController::class, 'taskGet'])->name('admin.task');
        Route::post('/task/{id}', [TaskController::class, 'taskPost'])->name('admin.task.update');
        Route::post('/task/{id}/approve', [TaskController::class, 'approveTask'])->name('admin.task.approve');
        Route::post('/task/{id}/reject', [TaskController::class, 'rejectTask'])->name('admin.task.reject');
        
        // WhatsApp
        Route::get('/whatsappnumbers', [WhatsAppController::class, 'whatsappnumbersGet'])->name('admin.whatsapp_numbers');
        Route::post('/whatsappnumbers/add', [WhatsAppController::class, 'addNumber'])->name('admin.add_whatsapp_number');
        Route::get('/verify-phone', [WhatsAppController::class, 'verifyPhoneGet'])->name('admin.verify_phone');
        Route::post('/verify-phone', [WhatsAppController::class, 'verifyPhonePost'])->name('admin.verify_phone.post');
        
        // Gestion des utilisateurs
// Pour la vue des utilisateurs
Route::get('/users_{group}', [UserController::class, 'usersGet'])->name('admin.users');

// Pour les actions sur les utilisateurs
Route::post('/users_{group}', [UserController::class, 'usersPost'])->name('admin.users');

        // Dashboard Annonceur
    Route::get('/client/dashboard', [DashboardController::class, 'announcerDashboard'])->name('admin.client.dashboard');
    
    // Route pour récupérer les affectations d'une tâche
    Route::get('/admin/task/{id}/assignments', [TaskController::class, 'getTaskAssignments'])->name('admin.task.assignments');

    // Dans le groupe Route::prefix('admin')->middleware(['auth'])
Route::get('/campaigns/create', [App\Http\Controllers\Web\Admin\DashboardAdminController::class, 'createCampaign'])->name('admin.campaigns.create');
Route::post('/campaigns', [App\Http\Controllers\Web\Admin\DashboardAdminController::class, 'storeCampaign'])->name('admin.campaigns.store');
    });
});



// Admin Dashboard Routes
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Tableau de bord
    Route::get('/dashboard', [App\Http\Controllers\Web\Admin\DashboardAdminController::class, 'index'])->name('admin.dashboard');
    
    // Catégories
    Route::get('/categories', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'index'])->name('admin.categories');
    Route::get('/categories/create', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{id}/edit', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{id}', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'destroy'])->name('admin.categories.destroy');
    
    // Campagnes
    /* Route::get('/campaigns', [App\Http\Controllers\Web\Admin\CampaignAdminController::class, 'index'])->name('admin.campaigns');
    Route::get('/campaigns/{id}', [App\Http\Controllers\Web\Admin\CampaignAdminController::class, 'show'])->name('admin.campaigns.show');
    Route::post('/campaigns/{id}/approve', [App\Http\Controllers\Web\Admin\CampaignAdminController::class, 'approve'])->name('admin.campaigns.approve');
    Route::post('/campaigns/{id}/reject', [App\Http\Controllers\Web\Admin\CampaignAdminController::class, 'reject'])->name('admin.campaigns.reject'); */
    
    // Finance
    Route::get('/finance', [App\Http\Controllers\Web\Admin\FinanceAdminController::class, 'index'])->name('admin.finance');
    Route::get('/finance/transactions', [App\Http\Controllers\Web\Admin\FinanceAdminController::class, 'transactions'])->name('admin.finance.transactions');
    Route::post('/finance/transactions/{id}/validate', [App\Http\Controllers\Web\Admin\FinanceAdminController::class, 'validatePayment'])->name('admin.finance.validate');
    
    // Rapports
   /*  Route::get('/reports', [App\Http\Controllers\Web\Admin\ReportAdminController::class, 'index'])->name('admin.reports');
    Route::get('/reports/usage', [App\Http\Controllers\Web\Admin\ReportAdminController::class, 'usageReport'])->name('admin.reports.usage');
    Route::get('/reports/performance', [App\Http\Controllers\Web\Admin\ReportAdminController::class, 'performanceReport'])->name('admin.reports.performance');
    Route::get('/reports/financial', [App\Http\Controllers\Web\Admin\ReportAdminController::class, 'financialReport'])->name('admin.reports.financial'); */
    
    // Paramètres système
   /*  Route::get('/settings', [App\Http\Controllers\Web\Admin\SystemSettingsController::class, 'index'])->name('admin.settings');
    Route::post('/settings', [App\Http\Controllers\Web\Admin\SystemSettingsController::class, 'updateSettings'])->name('admin.settings.update');
    Route::get('/settings/email', [App\Http\Controllers\Web\Admin\SystemSettingsController::class, 'emailSettings'])->name('admin.settings.email');
    Route::post('/settings/email', [App\Http\Controllers\Web\Admin\SystemSettingsController::class, 'updateEmailSettings'])->name('admin.settings.email.update');
    Route::get('/settings/whatsapp', [App\Http\Controllers\Web\Admin\SystemSettingsController::class, 'whatsappSettings'])->name('admin.settings.whatsapp');
    Route::post('/settings/whatsapp', [App\Http\Controllers\Web\Admin\SystemSettingsController::class, 'updateWhatsappSettings'])->name('admin.settings.whatsapp.update'); */
    
    // Support
   /*  Route::get('/support', [App\Http\Controllers\Web\Admin\SupportAdminController::class, 'index'])->name('admin.support');
    Route::get('/support/tickets/{id}', [App\Http\Controllers\Web\Admin\SupportAdminController::class, 'showTicket'])->name('admin.support.ticket');
    Route::post('/support/tickets/{id}/reply', [App\Http\Controllers\Web\Admin\SupportAdminController::class, 'replyToTicket'])->name('admin.support.ticket.reply');
    Route::post('/support/tickets/{id}/status', [App\Http\Controllers\Web\Admin\SupportAdminController::class, 'updateTicketStatus'])->name('admin.support.ticket.status');
    Route::get('/support/faq', [App\Http\Controllers\Web\Admin\SupportAdminController::class, 'faq'])->name('admin.support.faq'); */
    
    // Logs et audit
/*     Route::get('/logs', [App\Http\Controllers\Web\Admin\LogsAdminController::class, 'index'])->name('admin.logs');
    Route::get('/logs/security', [App\Http\Controllers\Web\Admin\LogsAdminController::class, 'securityAlerts'])->name('admin.logs.security'); */
});


require __DIR__ . '/annonceur.php';
require __DIR__ . '/influencer.php';

