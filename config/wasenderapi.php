<?php
// config/wasender.php - Configuration corrigée

return [
    /*
    |--------------------------------------------------------------------------
    | WaSender API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WaSender WhatsApp API integration
    |
    */

    // URL de base de l'API WaSender
    'api_url' => env('WASENDERAPI_URL', 'https://wasenderapi.com/api'),

    // Token d'authentification principal (Bearer Token)
    'personal_access_token' => env('WASENDERAPI_PERSONAL_ACCESS_TOKEN'),

    // Token API alternatif (si différent)
    'api_key' => env('WASENDERAPI_API_KEY'),

    // Configuration du webhook
    'webhook_secret' => env('WASENDERAPI_WEBHOOK_SECRET'),
    'webhook_route' => env('WASENDERAPI_WEBHOOK_ROUTE', '/api/whatsappnotifier'),

    // Configuration de sécurité
    'max_retry_attempts' => env('WASENDERAPI_MAX_RETRIES', 3),
    'timeout_seconds' => env('WASENDERAPI_TIMEOUT', 30),
    'rate_limit_per_minute' => env('WASENDERAPI_RATE_LIMIT', 10),

    // Logging
    'enable_logging' => env('WASENDERAPI_ENABLE_LOGGING', true),
    'log_level' => env('WASENDERAPI_LOG_LEVEL', 'info'),

    // Cache pour les vérifications de statut
    'cache_status_duration' => env('WASENDERAPI_CACHE_DURATION', 300), // 5 minutes
];