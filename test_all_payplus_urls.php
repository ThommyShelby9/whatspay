<?php

// File: test_all_payplus_urls.php
// Exécuter avec : php test_all_payplus_urls.php

require_once 'vendor/autoload.php';

$apiKey = '57DD7H4RBP8WVAM3D';
$apiToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZF9hcHAiOiI0NjgyIiwiaWRfYWJvbm5lIjoxMDc4MCwiZGF0ZWNyZWF0aW9uX2FwcCI6IjIwMjUtMTEtMDEgMDI6MTU6MTIifQ.aOirgkjSysUBnUUAQG6m9eJpZu0WAz1OInYbYAqX_rY';

echo "🧪 TEST EXHAUSTIF DES URLS PAYPLUS\n";
echo "==================================\n\n";

// Toutes les variations d'URL possibles
$baseUrls = [
    'https://api.payplus.africa',
    'https://payplus.africa', 
    'https://gateway.payplus.africa',
    'https://api-gateway.payplus.africa',
    'https://app.payplus.africa',
    'https://admin.payplus.africa',
    'https://portal.payplus.africa',
    'https://pay.payplus.africa',
    'https://secure.payplus.africa',
    'https://checkout.payplus.africa',
    'https://api.payplus.com',
    'https://api.payplus.ci',
    'https://payplus.ci',
    'https://api.payplus.net'
];

$endpoints = [
    '/pay/v01/redirect/checkout-invoice/create',
    '/api/v1/payments/create',
    '/api/v1/checkout/create',
    '/v1/payments/create',
    '/payments/create',
    '/checkout/create',
    '/invoice/create',
    '/api/payments',
    '/api/checkout'
];

$payload = [
    'commande' => [
        'invoice' => [
            'items' => [[
                'name' => 'Test',
                'quantity' => 1,
                'unit_price' => 1000,
                'total_price' => 1000
            ]],
            'total_amount' => 1000,
            'devise' => 'XOF',
            'customer' => '2250161368424',
            'external_id' => 'TEST-' . time()
        ],
        'actions' => [
            'callback_url' => 'https://test.com/callback',
            'callback_url_method' => 'post_json'
        ]
    ]
];

$workingUrls = [];

foreach ($baseUrls as $baseUrl) {
    echo "📍 Testing base URL: $baseUrl\n";
    
    // D'abord tester si le domaine répond
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "  ❌ Domain unreachable: $error\n";
        continue;
    }
    
    if ($httpCode >= 200 && $httpCode < 400) {
        echo "  ✅ Domain accessible ($httpCode)\n";
        
        // Tester les endpoints
        foreach ($endpoints as $endpoint) {
            $fullUrl = $baseUrl . $endpoint;
            echo "    🔗 $endpoint ... ";
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiToken,
                'Apikey: ' . $apiKey,
                'Accept: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                echo "❌ Error\n";
            } else {
                switch ($httpCode) {
                    case 200:
                    case 201:
                        echo "✅ SUCCESS ($httpCode)\n";
                        $workingUrls[] = $fullUrl;
                        echo "      Response: " . substr($response, 0, 100) . "...\n";
                        break;
                    case 400:
                        echo "⚠️  Bad Request ($httpCode) - API exists!\n";
                        $workingUrls[] = $fullUrl . ' (needs correct payload)';
                        break;
                    case 401:
                    case 403:
                        echo "🔐 Auth Required ($httpCode) - API exists!\n";
                        $workingUrls[] = $fullUrl . ' (auth issue)';
                        break;
                    case 404:
                        echo "❌ Not Found\n";
                        break;
                    case 405:
                        echo "⚠️  Method Not Allowed ($httpCode) - API exists!\n";
                        break;
                    default:
                        echo "❓ HTTP $httpCode\n";
                }
            }
        }
    } else {
        echo "  ❌ Domain returns $httpCode\n";
    }
    
    echo "\n";
}

echo "\n🎯 RÉSULTATS\n";
echo "============\n";

if (count($workingUrls) > 0) {
    echo "✅ URLs qui semblent fonctionner:\n";
    foreach ($workingUrls as $url) {
        echo "  - $url\n";
    }
} else {
    echo "❌ Aucune URL fonctionnelle trouvée\n";
    echo "\n🔧 ACTIONS RECOMMANDÉES:\n";
    echo "1. Contactez PayPlus pour obtenir la vraie URL d'API\n";
    echo "2. Vérifiez que votre token n'a pas expiré\n";
    echo "3. Demandez la documentation à jour\n";
    echo "4. Utilisez le mode simulation en attendant\n";
}

echo "\n📧 CONTACT PAYPLUS:\n";
echo "Email: donald.ablo@payplus.africa\n";
echo "Documentation: https://developers.payplus.africa\n";