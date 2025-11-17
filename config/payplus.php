<?php

// File: config/payplus.php

return [

    /*
    |--------------------------------------------------------------------------
    | PayPlus Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your PayPlus API credentials and settings.
    | You can get these credentials from your PayPlus merchant dashboard.
    |
    */

    'base_url' => env('PAYPLUS_BASE_URL', 'https://app.payplus.africa'),

    'api_key' => env('PAYPLUS_API_KEY', '57DD7H4RBP8WVAM3D'),

    'api_token' => env('PAYPLUS_API_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZF9hcHAiOiI0NjgyIiwiaWRfYWJvbm5lIjoxMDc4MCwiZGF0ZWNyZWF0aW9uX2FwcCI6IjIwMjUtMTEtMDEgMDI6MTU6MTIifQ.aOirgkjSysUBnUUAQG6m9eJpZu0WAz1OInYbYAqX_rY'),

    /*
    |--------------------------------------------------------------------------
    | Store Information
    |--------------------------------------------------------------------------
    |
    | This information will be sent to PayPlus for payment processing.
    |
    */

    'store' => [
        'name' => env('PAYPLUS_STORE_NAME', 'WhatsPAY'),
        'website_url' => env('APP_URL', 'https://whatspay.com'),
        'contact_email' => env('PAYPLUS_CONTACT_EMAIL', 'support@whatspay.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Limits
    |--------------------------------------------------------------------------
    |
    | Define minimum and maximum amounts for deposits and withdrawals.
    | Amounts are in XOF (FCFA).
    |
    */

    'limits' => [
        'deposit' => [
            'min' => env('PAYPLUS_MIN_DEPOSIT', 1000),
            'max' => env('PAYPLUS_MAX_DEPOSIT', 1000000),
        ],
        'withdrawal' => [
            'min' => env('PAYPLUS_MIN_WITHDRAWAL', 500),
            'max' => env('PAYPLUS_MAX_WITHDRAWAL', 500000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission Settings
    |--------------------------------------------------------------------------
    |
    | Platform commission settings for different transaction types.
    |
    */

    'commission' => [
        'platform_rate' => env('PAYPLUS_PLATFORM_COMMISSION', 0.10), // 10%
        'payment_processing_fee' => env('PAYPLUS_PROCESSING_FEE', 0), // Fixed fee
        'withdrawal_fee' => env('PAYPLUS_WITHDRAWAL_FEE', 0), // Fixed fee
    ],

    /*
    |--------------------------------------------------------------------------
    | Campaign Settings
    |--------------------------------------------------------------------------
    |
    | Settings related to campaign payments and view calculations.
    |
    */

    'campaign' => [
        'view_rate' => env('PAYPLUS_VIEW_RATE', 3), // 3 FCFA per view
        'auto_payment_enabled' => env('PAYPLUS_AUTO_PAYMENT', true),
        'payment_delay_hours' => env('PAYPLUS_PAYMENT_DELAY', 24), // Hours to wait before auto payment
    ],

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | URLs for PayPlus to send callback notifications.
    |
    */

    'callbacks' => [
        'deposit_success' => env('APP_URL') . '/payment/callback/{transaction}',
        'withdrawal_success' => env('APP_URL') . '/payment/callback/withdrawal/{transaction}',
        'return_url' => env('APP_URL') . '/announcer/wallet?status=success',
        'cancel_url' => env('APP_URL') . '/announcer/wallet?status=cancelled',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging of PayPlus API interactions.
    |
    */

    'logging' => [
        'enabled' => env('PAYPLUS_LOGGING', true),
        'level' => env('PAYPLUS_LOG_LEVEL', 'info'),
        'channel' => env('PAYPLUS_LOG_CHANNEL', 'single'),
    ],

];
