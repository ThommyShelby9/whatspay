<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', 
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
    $middleware->trustProxies(headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_ALL);
    
    // Désactiver CSRF temporairement pour test
    $middleware->validateCsrfTokens(except: ['*']);
        
        // ✅ CSRF explicite pour Laravel 12
        $middleware->validateCsrfTokens(except: [
            // routes à exclure si nécessaire
        ]);
        
        // ✅ Configuration sessions explicite 
        $middleware->encryptCookies(except: []);
        
        // ✅ Ordre des middlewares critique
        $middleware->priority([
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class, 
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();