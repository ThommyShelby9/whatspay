<?php

/**
 * Script pour traiter une transaction en attente et créditer le wallet
 *
 * Utilisation : php fix_pending_transaction.php [TRANSACTION_ID]
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Carbon\Carbon;

// Récupérer l'ID de transaction depuis les arguments
$transactionId = $argv[1] ?? 'c871f3db-ad43-46c5-9338-fe55ec7786bf';

echo "\n";
echo "========================================\n";
echo "  CORRECTION TRANSACTION EN ATTENTE\n";
echo "========================================\n\n";

echo "Transaction ID: {$transactionId}\n\n";

// 1. Récupérer la transaction
echo "1. Récupération de la transaction...\n";
$transaction = PaymentTransaction::find($transactionId);

if (!$transaction) {
    echo "   ✗ Transaction non trouvée !\n\n";
    exit(1);
}

echo "   ✓ Transaction trouvée\n";
echo "   - User ID: {$transaction->user_id}\n";
echo "   - Montant: {$transaction->amount} {$transaction->currency}\n";
echo "   - Status actuel: {$transaction->status}\n";
echo "   - Référence: {$transaction->reference}\n\n";

// 2. Vérifier la réponse PayPlus
echo "2. Analyse de la réponse PayPlus...\n";
$gatewayResponse = json_decode($transaction->gateway_response, true);

if (!$gatewayResponse) {
    echo "   ✗ Pas de réponse PayPlus enregistrée\n\n";
    exit(1);
}

echo "   - Response code: " . ($gatewayResponse['response_code'] ?? 'N/A') . "\n";
echo "   - Token: " . (isset($gatewayResponse['token']) ? '✓ Présent' : '✗ Absent') . "\n\n";

// 3. Vérifier le statut avec PayPlus
echo "3. Vérification du statut avec PayPlus...\n";
$paymentService = app(PaymentService::class);
$statusCheck = $paymentService->checkTransactionStatus($transactionId);

echo "   - Vérification: " . ($statusCheck['success'] ? '✓ OK' : '✗ Échec') . "\n";

if ($statusCheck['success']) {
    echo "   - Statut PayPlus: " . ($statusCheck['status'] ?? 'N/A') . "\n";
    echo "   - Response code: " . ($statusCheck['response_code'] ?? 'N/A') . "\n\n";

    // 4. Si la transaction est complétée chez PayPlus, créditer le wallet
    if ($statusCheck['status'] === 'completed' || $gatewayResponse['response_code'] === '00') {
        echo "4. Transaction confirmée complétée ! Traitement...\n";

        // Vérifier si déjà complétée localement
        if ($transaction->status === 'COMPLETED') {
            echo "   ⚠️  Transaction déjà marquée comme complétée localement\n";
            echo "   Vérifiez si le wallet a bien été crédité.\n\n";
        } else {
            // Simuler le callback de PayPlus
            $callbackData = [
                'response_code' => '00',
                'description' => 'completed',
                'transaction_id' => $transaction->reference,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'manual_fix' => true,
                'fixed_at' => Carbon::now()->toDateTimeString()
            ];

            echo "   - Traitement du callback...\n";
            $success = $paymentService->processDepositCallback($transactionId, $callbackData);

            if ($success) {
                echo "   ✓ Wallet crédité avec succès !\n";
                echo "   ✓ Transaction marquée comme complétée\n\n";

                // Afficher le nouveau solde
                $walletService = app(\App\Services\WalletService::class);
                $newBalance = $walletService->getBalance($transaction->user_id);
                echo "   Nouveau solde du wallet: {$newBalance} FCFA\n\n";
            } else {
                echo "   ✗ Échec du traitement du callback\n";
                echo "   Vérifiez les logs pour plus de détails\n\n";
            }
        }
    } else {
        echo "4. ⚠️  Transaction pas encore complétée chez PayPlus\n";
        echo "   Statut actuel: " . ($statusCheck['status'] ?? 'N/A') . "\n";
        echo "   Attendez quelques minutes et réessayez.\n\n";
    }
} else {
    echo "   ⚠️  Impossible de vérifier le statut avec PayPlus\n";
    echo "   Message: " . ($statusCheck['message'] ?? 'N/A') . "\n\n";

    // En cas d'échec de vérification, mais si on a response_code = '00'
    if ($gatewayResponse['response_code'] === '00') {
        echo "4. Response code = '00' détecté, traitement forcé...\n";

        $callbackData = [
            'response_code' => '00',
            'description' => 'completed',
            'transaction_id' => $transaction->reference,
            'amount' => $transaction->amount,
            'currency' => $transaction->currency,
            'manual_fix' => true,
            'forced_completion' => true,
            'fixed_at' => Carbon::now()->toDateTimeString()
        ];

        echo "   - Traitement du callback (mode forcé)...\n";
        $success = $paymentService->processDepositCallback($transactionId, $callbackData);

        if ($success) {
            echo "   ✓ Wallet crédité avec succès !\n";
            echo "   ✓ Transaction marquée comme complétée\n\n";

            $walletService = app(\App\Services\WalletService::class);
            $newBalance = $walletService->getBalance($transaction->user_id);
            echo "   Nouveau solde du wallet: {$newBalance} FCFA\n\n";
        } else {
            echo "   ✗ Échec du traitement\n\n";
        }
    }
}

echo "========================================\n";
echo "  FIN\n";
echo "========================================\n\n";
