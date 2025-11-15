<?php
// config/wasender.php

return [
    'api_key' => env('WASENDERAPI_API_KEY', ''),
    'personal_access_token' => env('WASENDERAPI_PERSONAL_ACCESS_TOKEN', '8bf6acbe4d6ff72609013382761658be53a4eeba0a961946a8073538eaf840f5'),
    'api_url' => env('WASENDERAPI_URL', 'https://wasenderapi.com/api'),
    'webhook_secret' => env('WASENDERAPI_WEBHOOK_SECRET', ''),
    'webhook_route' => env('WASENDERAPI_WEBHOOK_ROUTE', '/wasender/webhook'),
    'webhook_signature_header' => env('WASENDERAPI_WEBHOOK_SIGNATURE_HEADER', 'x-webhook-signature'),
];