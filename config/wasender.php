<?php

// config/wasender.php - Version minimale garantie

return [
    'personal_access_token' => env('WASENDERAPI_PERSONAL_ACCESS_TOKEN'),
    'api_url' => env('WASENDERAPI_URL', 'https://wasenderapi.com/api'),
    'webhook_secret' => env('WASENDERAPI_WEBHOOK_SECRET'),
    'max_retry_attempts' => 3,
    'timeout_seconds' => 30,
    'enable_logging' => true,
];