<?php

/**
 * Script pour forcer le traitement des transactions qui ont response_code='00'
 * mais sont toujours en PENDING
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

echo "\n";
echo "========================================\n";
echo "  FORCER LE TRAITEMENT DES TRANSACTIONS\n";
echo "========================================\n\n";

// Récupérer les transactions PENDING avec response_code = '00'
$pendingTransactions = PaymentTransaction::where('status', 'PENDING')
    ->whereNotNull('gateway_response')
    ->get()
    ->filter(function ($transaction) {
        $gatewayResponse = json_decode($transaction->gateway_response, true);
        return isset($gatewayResponse['response_code']) && $gatewayResponse['response_code'] === '00';
    });

if ($pendingTransactions->isEmpty()) {
    echo "✓ Aucune transaction à traiter.\n\n";
    exit(0);
}

echo "Trouvé {$pendingTransactions->count()} transaction(s) à traiter\n\n";

$paymentService = app(PaymentService::class);

foreach ($pendingTransactions as $transaction) {
    echo "Transaction: {$transaction->reference}\n";
    echo "  - ID: {$transaction->id}\n";
    echo "  - Montant: {$transaction->amount} {$transaction->currency}\n";
    echo "  - User ID: {$transaction->user_id}\n";
    echo "  - Créée: {$transaction->created_at}\n";

    $gatewayResponse = json_decode($transaction->gateway_response, true);
    echo "  - Response Code: " . $gatewayResponse['response_code'] . "\n";
    echo "  - Token: " . (isset($gatewayResponse['token']) ? '✓ Présent' : '✗ Absent') . "\n";

    // Déterminer le type de transaction
    $payload = json_decode($transaction->payload, true);
    $type = $payload['type'] ?? 'deposit';

    echo "  - Type: {$type}\n";

    // Demander confirmation
    echo "\n";
    echo "  Traiter cette transaction ? (o/n) : ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    $confirm = trim(strtolower($line));
    fclose($handle);

    if ($confirm !== 'o' && $confirm !== 'y' && $confirm !== 'yes' && $confirm !== 'oui') {
        echo "  ⏭️  Ignorée\n\n";
        continue;
    }

    // Préparer les données du callback
    $callbackData = [
        'response_code' => '00',
        'description' => 'completed',
        'transaction_id' => $transaction->reference,
        'amount' => abs($transaction->amount),
        'currency' => $transaction->currency,
        'forced_completion' => true,
        'completed_at' => Carbon::now()->toDateTimeString()
    ];

    echo "  → Traitement en cours...\n";

    try {
        if ($type === 'withdrawal') {
            $success = $paymentService->processWithdrawalCallback($transaction->id, $callbackData);
        } else {
            $success = $paymentService->processDepositCallback($transaction->id, $callbackData);
        }

        if ($success) {
            echo "  ✅ SUCCÈS ! Transaction traitée\n";

            // Afficher le nouveau solde
            $walletService = app(\App\Services\WalletService::class);
            $newBalance = $walletService->getBalance($transaction->user_id);
            echo "  💰 Nouveau solde: {$newBalance} FCFA\n";

            Log::info('Forced completion of transaction', [
                'transaction_id' => $transaction->id,
                'type' => $type,
                'amount' => $transaction->amount
            ]);
        } else {
            echo "  ❌ ÉCHEC ! Le traitement a échoué\n";
            echo "  → Vérifiez les logs pour plus de détails\n";
        }

    } catch (\Exception $e) {
        echo "  ❌ ERREUR : " . $e->getMessage() . "\n";
        Log::error('Error forcing completion', [
            'transaction_id' => $transaction->id,
            'error' => $e->getMessage()
        ]);
    }

    echo "\n";
}

echo "========================================\n";
echo "  TERMINÉ\n";
echo "========================================\n\n";
