<?php
// Script de test pour l'API WaSender

echo "=== Test de l'API WaSender ===\n\n";

$tokens = [
    'token1' => '8bf6acbe4d6ff72609013382761658be53a4eeba0a961946a8073538eaf840f5',
    'token2' => '1464|Vkrv6IWAOByTXGn9J8oxL2WvO4jRplf3F6LyPN1Bd8512f2d'
];

$apiUrl = 'https://wasenderapi.com/api';

function testToken($token, $name) {
    global $apiUrl;
    
    echo "Test du $name : " . substr($token, 0, 10) . "...\n";
    
    // Test du statut de l'API
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl . '/status',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "  - Code HTTP: $httpCode\n";
    if ($error) {
        echo "  - Erreur cURL: $error\n";
    }
    echo "  - Réponse: " . substr($response, 0, 200) . "\n\n";
    
    return $httpCode === 200;
}

foreach ($tokens as $name => $token) {
    $success = testToken($token, $name);
    if ($success) {
        echo "✅ $name fonctionne !\n\n";
    } else {
        echo "❌ $name ne fonctionne pas\n\n";
    }
}

// Test avec un endpoint différent si /status ne fonctionne pas
echo "=== Test avec endpoint alternatif ===\n";
foreach ($tokens as $name => $token) {
    echo "Test du $name avec /me :\n";
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl . '/me',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "  - Code HTTP: $httpCode\n";
    echo "  - Réponse: " . substr($response, 0, 200) . "\n\n";
}