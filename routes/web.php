
<?php

use App\Http\Controllers\Api\TrackingApiController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WhatsAppController;
use App\Http\Controllers\Web\Admin\DashboardAdminController;
use App\Http\Controllers\Web\Admin\WhatsAppMessagingController;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;



// Redirection vers la page de login
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

/*
|--------------------------------------------------------------------------
| Routes publiques (non authentifiées)
|--------------------------------------------------------------------------
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/page/{page}', [PageController::class, 'page'])->name('page');
Route::get('/comingsoon', [PageController::class, 'comingsoon'])->name('comingsoon');

/*
|--------------------------------------------------------------------------
| Routes d'authentification
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Routes d'accès/login
    Route::get('/', [AuthController::class, 'loginGet'])->name('admin.index');
    Route::get('/login', [AuthController::class, 'loginGet'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'loginPost'])->name('admin.login.post');

    // Routes d'inscription
    Route::get('/registration', [AuthController::class, 'registrationHomeGet'])->name('admin.register');
    Route::get('/registration/diffuseur', [AuthController::class, 'registrationDiffuseurGet'])->name('admin.register.diffuseur');
    Route::get('/registration/annonceur', [AuthController::class, 'registrationAnnonceurGet'])->name('admin.register.annonceur');
    Route::post('/registration/diffuseur', [AuthController::class, 'registrationDiffuseurPost'])->name('admin.register.diffuseur.post');
    Route::post('/registration/annonceur', [AuthController::class, 'registrationAnnonceurPost'])->name('admin.register.annonceur.post');
    Route::post('/registration', [AuthController::class, 'registrationPost'])->name('admin.register.post');

    // Vérification de compte et récupération de mot de passe
    Route::get('/verify-account', [AuthController::class, 'verifyAccountGet'])->name('admin.verify_account');
    Route::post('/verify-account', [AuthController::class, 'verifyAccountPost'])->name('admin.verify_account.post');
    Route::get('/forgotten_password', [AuthController::class, 'forgotten_passwordPageGet'])->name('admin.forgotten_password');
    Route::post('/forgotten_password', [AuthController::class, 'forgotten_passwordPagePost'])->name('admin.forgotten_password.post');
    Route::get('/password_recovery/{token}', [AuthController::class, 'reset_passwordGet'])->name('admin.password_recovery');
    Route::post('/password_recovery/{token}', [AuthController::class, 'reset_passwordPost'])->name('admin.password_recovery.post');
    Route::get('/twofa_auth/{token}', [AuthController::class, 'twofa_authGet'])->name('admin.twofa_auth');
    Route::post('/twofa_auth/{token}', [AuthController::class, 'twofa_authPost'])->name('admin.twofa_auth.post');
});

/*
|--------------------------------------------------------------------------
| Routes protégées (authentifiées)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Déconnexion et tableau de bord
    Route::get('/logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', [DashboardController::class, 'dashboardGet'])->name('admin.dashboard');

    // Profil utilisateur
    Route::get('/myprofile', [UserController::class, 'profile'])->name('admin.myprofile');
    Route::post('/myprofile', [UserController::class, 'updateProfile'])->name('admin.myprofile.update');

    /*
    |--------------------------------------------------------------------------
    | Routes pour les campagnes (TaskController)
    |--------------------------------------------------------------------------
    */
    // Liste des campagnes
    Route::get('/tasks', [TaskController::class, 'tasksGet'])->name('admin.tasks');

    // Détails/formulaire de campagne
    Route::get('/task/{id}', [TaskController::class, 'taskGet'])->name('admin.task');
    Route::post('/task/{id}', [TaskController::class, 'taskPost'])->name('admin.task.update');

    // Actions sur les campagnes
    Route::post('/task/{id}/approve', [TaskController::class, 'approveTask'])->name('admin.task.approve');
    Route::post('/task/{id}/reject', [TaskController::class, 'rejectTask'])->name('admin.task.reject');
    Route::post('/task/{id}/delete', [TaskController::class, 'deleteTask'])->name('admin.task.delete');

    // API pour les assignations
    Route::get('/task/{id}/assignments', [TaskController::class, 'getTaskAssignments'])->name('admin.task.assignments');

    // Compatibilité avec les nouvelles routes de campagnes
    Route::get('/campaigns/create', function () {
        return redirect()->route('admin.task', ['id' => 'new']);
    })->name('admin.campaigns.create');

    Route::post('/campaigns', function (Illuminate\Http\Request $request) {
        return app()->call([app()->make(TaskController::class), 'taskPost'], ['request' => $request, 'id' => 'new']);
    })->name('admin.campaigns.store');

    Route::get('/campaigns/{id}', function ($id) {
        return redirect()->route('admin.task', ['id' => $id]);
    })->name('admin.campaigns.show');

    Route::put('/campaigns/{id}', function (Illuminate\Http\Request $request, $id) {
        return app()->call([app()->make(TaskController::class), 'taskPost'], ['request' => $request, 'id' => $id]);
    })->name('admin.campaigns.update');

    /*
    |--------------------------------------------------------------------------
    | Routes pour la gestion WhatsApp
    |--------------------------------------------------------------------------
    */
    Route::get('/whatsappnumbers', [WhatsAppController::class, 'whatsappnumbersGet'])->name('admin.whatsapp_numbers');
    Route::post('/whatsappnumbers/add', [WhatsAppController::class, 'addNumber'])->name('admin.add_whatsapp_number');
    Route::get('/verify-phone', [WhatsAppController::class, 'verifyPhoneGet'])->name('admin.verify_phone');
    Route::post('/verify-phone', [WhatsAppController::class, 'verifyPhonePost'])->name('admin.verify_phone.post');

    /*
    |--------------------------------------------------------------------------
    | Routes pour la gestion des utilisateurs
    |--------------------------------------------------------------------------
    */
    Route::get('/users_{group}', [UserController::class, 'usersGet'])->name('admin.users');
    Route::post('/users_{group}', [UserController::class, 'usersPost']);
    Route::post('/users_{group}/delete/{userId}', [UserController::class, 'deleteUserById'])->name('admin.users.delete');

    // Dashboard Annonceur
    Route::get('/client/dashboard', [DashboardController::class, 'announcerDashboard'])->name('admin.client.dashboard');
});

/*
|--------------------------------------------------------------------------
| Routes Admin avancées
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Catégories
    Route::get('/categories', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'index'])->name('admin.categories');
    Route::get('/categories/create', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'create'])->name('admin.categories.create');
    Route::post('/categories', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'store'])->name('admin.categories.store');
    Route::get('/categories/{id}/edit', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'edit'])->name('admin.categories.edit');
    Route::put('/categories/{id}', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'update'])->name('admin.categories.update');
    Route::delete('/categories/{id}', [App\Http\Controllers\Web\Admin\CategoryAdminController::class, 'destroy'])->name('admin.categories.destroy');

    // Finance
    Route::get('/finance', [App\Http\Controllers\Web\Admin\FinanceAdminController::class, 'index'])->name('admin.finance');
    Route::get('/finance/transactions', [App\Http\Controllers\Web\Admin\FinanceAdminController::class, 'transactions'])->name('admin.finance.transactions');
    Route::post('/finance/transactions/{id}/validate', [App\Http\Controllers\Web\Admin\FinanceAdminController::class, 'validatePayment'])->name('admin.finance.validate');
});

//Enrégistrer un clic
Route::get('/track/{id}', [TrackingApiController::class, 'trackClick'])->name('api.track_click');
// File: routes/web.php (add these routes)

// Admin WhatsApp messaging routes
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/whatsapp-messaging', [WhatsAppMessagingController::class, 'index'])
        ->name('admin.whatsapp_messaging');
    Route::post('/whatsapp-messaging/send', [WhatsAppMessagingController::class, 'sendMassMessage'])
        ->name('admin.whatsapp_messaging.send');
});

/*
|--------------------------------------------------------------------------
| Routes de test WhatsApp (à supprimer en production)
|--------------------------------------------------------------------------
*/
Route::get('/sendmessage/{recipient}', [WhatsAppController::class, 'sendMessage'])->name('send_message');
Route::get('/sendmessage2/{recipient}/{message?}', [WhatsAppController::class, 'sendMessage2'])->name('send_message2');
// Route de debug temporaire
// routes/web.php - debug temporaire
Route::get('/debug-session', function () {
    try {
        // Test de connexion à la base de données
        $dbConnected = DB::connection()->getPdo() ? true : false;

        // Vérifier si la table sessions existe
        $sessionsTableExists = Schema::hasTable('sessions');

        // Compter les sessions
        $sessionsCount = $sessionsTableExists ? DB::table('sessions')->count() : 'N/A';

        return response()->json([
            'status' => 'OK',
            'database' => [
                'connected' => $dbConnected,
                'sessions_table_exists' => $sessionsTableExists,
                'sessions_count' => $sessionsCount,
            ],
            'session' => [
                'driver' => config('session.driver'),
                'csrf_token' => csrf_token(),
                'session_id' => session()->getId(),
                'session_token' => session()->token(),
                'domain' => config('session.domain'),
                'secure' => config('session.secure'),
                'lifetime' => config('session.lifetime'),
                'same_site' => config('session.same_site'),
            ],
            'request' => [
                'scheme' => request()->getScheme(),
                'is_secure' => request()->isSecure(),
                'url' => request()->url(),
                'full_url' => request()->fullUrl(),
            ],
            'headers' => [
                'x-forwarded-proto' => request()->header('X-Forwarded-Proto'),
                'x-forwarded-host' => request()->header('X-Forwarded-Host'),
                'x-forwarded-port' => request()->header('X-Forwarded-Port'),
                'x-forwarded-for' => request()->header('X-Forwarded-For'),
                'host' => request()->header('Host'),
            ],
            'cookies' => [
                'session_cookie_name' => config('session.cookie'),
                'cookies_received' => array_keys(request()->cookies->all()),
            ],
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'ERROR',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

Route::get('/test-login', function () {
    return '<form method="POST" action="/admin/login">
        ' . csrf_field() . '
        <input type="email" name="email" value="admin@lapieuvre.tech" required>
        <input type="password" name="password" value="zsedrftgyhuji" required>
        <button type="submit">Login Direct (sans JS)</button>
    </form>';
});

// Route de test PayPlus API
Route::get('/test-payplus', function () {
    $baseUrl = config('payplus.base_url');
    $apiKey = config('payplus.api_key');
    $apiToken = config('payplus.api_token');

    $results = [];

    // Test 1: Ping base URL
    try {
        $response = Http::timeout(10)->get($baseUrl);
        $results['base_url_test'] = [
            'url' => $baseUrl,
            'status' => $response->status(),
            'success' => $response->successful(),
            'body' => substr($response->body(), 0, 500)
        ];
    } catch (\Exception $e) {
        $results['base_url_test'] = [
            'url' => $baseUrl,
            'error' => $e->getMessage()
        ];
    }

    // Test 2: Test endpoints communs
    $endpoints = [
        '/pay/v01/redirect/checkout-invoice/create',
        '/api/v01/redirect/checkout-invoice/create',
        '/v01/redirect/checkout-invoice/create',
        '/checkout-invoice/create',
    ];

    foreach ($endpoints as $endpoint) {
        $fullUrl = $baseUrl . $endpoint;
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Apikey' => $apiKey,
                    'Content-Type' => 'application/json'
                ])
                ->post($fullUrl, [
                    'test' => 'ping'
                ]);

            $results['endpoints'][$endpoint] = [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => substr($response->body(), 0, 200)
            ];
        } catch (\Exception $e) {
            $results['endpoints'][$endpoint] = [
                'error' => $e->getMessage()
            ];
        }
    }

    $results['config'] = [
        'base_url' => $baseUrl,
        'has_api_key' => !empty($apiKey),
        'has_token' => !empty($apiToken),
        'api_key_length' => strlen($apiKey),
        'token_length' => strlen($apiToken)
    ];

    return response()->json($results, 200, [], JSON_PRETTY_PRINT);
});

// Route de debug pour voir les middlewares actifs
Route::get('/debug-middlewares', function () {
    $router = app('router');
    $routes = $router->getRoutes();
    
    foreach ($routes as $route) {
        if ($route->uri() === 'admin/login') {
            return [
                'middlewares' => $route->middleware(),
                'action' => $route->getAction(),
            ];
        }
    }
});
/*
|--------------------------------------------------------------------------
| Include Payment Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/payment.php';

/*
|--------------------------------------------------------------------------
| Include Specific Module Routes
|--------------------------------------------------------------------------
*/
require __DIR__ . '/annonceur.php';
require __DIR__ . '/influencer.php';
