<?php

/**
 * Script de diagnostic PayPlus - Test de dépôt
 *
 * Ce script teste l'API PayPlus et affiche les détails des erreurs
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

echo "\n";
echo "========================================\n";
echo "  TEST DIAGNOSTIC PAYPLUS - DÉPÔT\n";
echo "========================================\n\n";

// 1. Vérifier la configuration
echo "1. Vérification de la configuration PayPlus...\n";
echo "   - Base URL: " . config('payplus.base_url') . "\n";
echo "   - API Key: " . substr(config('payplus.api_key'), 0, 10) . "..." . "\n";
echo "   - API Token: " . (config('payplus.api_token') ? "✓ Configuré" : "✗ Manquant") . "\n\n";

// 2. Paramètres de test
$testAmount = 100; // 100 FCFA
$testPhone = '22997000000'; // Remplacer par votre numéro de test

echo "2. Paramètres de test:\n";
echo "   - Montant: {$testAmount} FCFA\n";
echo "   - Téléphone: {$testPhone}\n";
echo "   - IMPORTANT: Modifiez \$testPhone avec votre vrai numéro!\n\n";

// 3. Préparer le payload selon la doc PayPlus
$transactionId = 'TEST-' . time();
$externalId = 'DEP-TEST-' . time();

$payload = [
    'commande' => [
        'invoice' => [
            'items' => [
                [
                    'name' => 'Test Rechargement WhatsPAY',
                    'description' => 'Test diagnostic',
                    'quantity' => 1,
                    'unit_price' => $testAmount,
                    'total_price' => $testAmount
                ]
            ],
            'total_amount' => $testAmount,
            'devise' => 'XOF',
            'description' => 'Test diagnostic PayPlus',
            'customer' => $testPhone,
            'customer_firstname' => 'Test',
            'customer_lastname' => 'Diagnostic',
            'customer_email' => 'test@whatspay.africa',
            'external_id' => $externalId,
            'otp' => '',
            'network' => ''
        ],
        'store' => [
            'name' => config('payplus.store.name', 'WhatsPAY'),
            'website_url' => config('app.url')
        ],
        'actions' => [
            'cancel_url' => config('app.url') . '?status=cancelled',
            'return_url' => config('app.url') . '?status=success',
            'callback_url' => config('app.url') . '/payment/callback/' . $transactionId,
            'callback_url_method' => 'post_json'
        ],
        'custom_data' => [
            'transaction_id' => $transactionId,
            'hash' => hash('sha256', $transactionId . $testAmount)
        ]
    ]
];

echo "3. Payload préparé ✓\n\n";

// 4. Tester l'API PayPlus
echo "4. Test de l'API PayPlus...\n";

$baseUrl = config('payplus.base_url');
$endpoint = '/pay/v01/redirect/checkout-invoice/create';
$fullUrl = $baseUrl . $endpoint;

$headers = [
    'Content-Type' => 'application/json',
    'Authorization' => 'Bearer ' . config('payplus.api_token'),
    'Apikey' => config('payplus.api_key')
];

echo "   - URL: {$fullUrl}\n";
echo "   - Envoi de la requête...\n\n";

try {
    $response = Http::timeout(30)
        ->withHeaders($headers)
        ->post($fullUrl, $payload);

    $statusCode = $response->status();
    $responseData = $response->json();

    echo "========================================\n";
    echo "  RÉSULTAT\n";
    echo "========================================\n\n";

    echo "Status HTTP: {$statusCode}\n";
    echo "Response Code: " . ($responseData['response_code'] ?? 'N/A') . "\n\n";

    if (isset($responseData['response_code'])) {
        if ($responseData['response_code'] === '00') {
            echo "✓ SUCCÈS!\n\n";
            echo "Token: " . ($responseData['token'] ?? 'N/A') . "\n";
            echo "Redirect URL: " . ($responseData['response_text'] ?? 'N/A') . "\n";
        } else {
            echo "✗ ÉCHEC - Code d'erreur: " . $responseData['response_code'] . "\n\n";
            echo "Description de l'erreur:\n";
            echo "   " . ($responseData['description'] ?? 'Aucune description') . "\n\n";

            // Diagnostics détaillés selon le code d'erreur
            if ($responseData['response_code'] === '01') {
                echo "DIAGNOSTIC pour code '01':\n";
                echo "   → Vérifiez votre API Key et Token dans config/payplus.php\n";
                echo "   → Vérifiez que votre compte PayPlus est actif\n";
                echo "   → Vérifiez que le numéro de téléphone est valide\n";
                echo "   → Vérifiez que les opérateurs Mobile Money sont activés sur votre compte\n";
                echo "   → Contactez le support PayPlus avec ce message d'erreur\n";
            }
        }
    }

    echo "\n";
    echo "Réponse complète de PayPlus:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 5. Recommandations
    echo "========================================\n";
    echo "  RECOMMANDATIONS\n";
    echo "========================================\n\n";

    if ($responseData['response_code'] !== '00') {
        echo "1. Vérifiez vos credentials PayPlus:\n";
        echo "   - Connectez-vous sur https://app.payplus.africa\n";
        echo "   - Vérifiez votre API Key et Token\n";
        echo "   - Vérifiez que votre compte est actif\n\n";

        echo "2. Vérifiez la configuration des opérateurs:\n";
        echo "   - Assurez-vous que MTN, Moov, etc. sont activés\n";
        echo "   - Vérifiez les limites de montant configurées\n\n";

        echo "3. Testez avec le numéro de téléphone correct:\n";
        echo "   - Format attendu: 229XXXXXXXX (avec indicatif pays)\n";
        echo "   - Exemple: 22997000000\n\n";

        echo "4. Contactez le support PayPlus:\n";
        echo "   - Email: support@payplus.africa\n";
        echo "   - Mentionnez le code d'erreur: " . $responseData['response_code'] . "\n";
        echo "   - Mentionnez votre API Key: " . substr(config('payplus.api_key'), 0, 10) . "...\n\n";
    } else {
        echo "✓ Votre configuration PayPlus fonctionne correctement!\n";
        echo "  Vous pouvez maintenant faire des dépôts en production.\n\n";
    }

} catch (\Exception $e) {
    echo "✗ ERREUR EXCEPTION:\n";
    echo "   " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . "\n";
    echo "   Ligne: " . $e->getLine() . "\n\n";
}

echo "========================================\n";
echo "  FIN DU TEST\n";
echo "========================================\n\n";
