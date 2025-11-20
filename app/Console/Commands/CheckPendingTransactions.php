<?php

namespace App\Console\Commands;

use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckPendingTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:check-pending
                            {--limit=50 : Nombre maximum de transactions à vérifier}
                            {--age=5 : Âge minimum en minutes des transactions à vérifier}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifie les transactions en attente et met à jour leur statut avec PayPlus';

    protected $paymentService;

    /**
     * Create a new command instance.
     */
    public function __construct(PaymentService $paymentService)
    {
        parent::__construct();
        $this->paymentService = $paymentService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = $this->option('limit');
        $ageMinutes = $this->option('age');

        $this->info("🔍 Vérification des transactions en attente...");
        $this->info("   Limite: {$limit} transactions");
        $this->info("   Âge minimum: {$ageMinutes} minutes\n");

        // Récupérer les transactions en attente
        $pendingTransactions = PaymentTransaction::where('status', 'PENDING')
            ->where('created_at', '<=', Carbon::now()->subMinutes($ageMinutes))
            ->where('expires_at', '>', Carbon::now()) // Pas expirées
            ->limit($limit)
            ->get();

        if ($pendingTransactions->isEmpty()) {
            $this->info("✓ Aucune transaction en attente à vérifier.");
            return 0;
        }

        $this->info("📋 {$pendingTransactions->count()} transactions trouvées\n");

        $stats = [
            'checked' => 0,
            'completed' => 0,
            'failed' => 0,
            'still_pending' => 0,
            'errors' => 0
        ];

        foreach ($pendingTransactions as $transaction) {
            $stats['checked']++;

            $this->line("Transaction: {$transaction->reference}");
            $this->line("  - ID: {$transaction->id}");
            $this->line("  - Montant: {$transaction->amount} {$transaction->currency}");
            $this->line("  - Créée: {$transaction->created_at->diffForHumans()}");

            try {
                // Vérifier le statut avec PayPlus
                $statusCheck = $this->paymentService->checkTransactionStatus($transaction->id);

                if ($statusCheck['success']) {
                    $payPlusStatus = $statusCheck['status'] ?? 'unknown';
                    $this->line("  - Statut PayPlus: {$payPlusStatus}");

                    // Parser le payload pour déterminer le type
                    $payload = json_decode($transaction->payload, true);
                    $type = $payload['type'] ?? 'deposit';

                    if ($payPlusStatus === 'completed') {
                        // Transaction complétée, traiter le callback
                        $this->info("  ✓ Complétée ! Traitement du callback...");

                        $callbackData = [
                            'response_code' => '00',
                            'description' => 'completed',
                            'transaction_id' => $transaction->reference,
                            'amount' => abs($transaction->amount),
                            'currency' => $transaction->currency,
                            'auto_checked' => true,
                            'checked_at' => Carbon::now()->toDateTimeString()
                        ];

                        if ($type === 'withdrawal') {
                            $success = $this->paymentService->processWithdrawalCallback($transaction->id, $callbackData);
                        } else {
                            $success = $this->paymentService->processDepositCallback($transaction->id, $callbackData);
                        }

                        if ($success) {
                            $this->info("  ✓ Wallet mis à jour avec succès\n");
                            $stats['completed']++;

                            Log::info('Auto-completed pending transaction', [
                                'transaction_id' => $transaction->id,
                                'type' => $type,
                                'amount' => $transaction->amount
                            ]);
                        } else {
                            $this->error("  ✗ Échec de la mise à jour du wallet\n");
                            $stats['errors']++;
                        }

                    } elseif ($payPlusStatus === 'notcompleted' || $payPlusStatus === 'failed') {
                        // Transaction échouée
                        $this->warn("  ✗ Échouée");

                        $transaction->update([
                            'status' => 'FAILED'
                        ]);

                        $stats['failed']++;

                        Log::warning('Transaction marked as failed', [
                            'transaction_id' => $transaction->id,
                            'payplus_status' => $payPlusStatus
                        ]);

                        $this->line("");

                    } else {
                        // Toujours en attente
                        $this->line("  ⏳ Toujours en attente chez PayPlus\n");
                        $stats['still_pending']++;
                    }

                } else {
                    // Impossible de vérifier
                    $this->warn("  ⚠️  Impossible de vérifier: " . ($statusCheck['message'] ?? 'Erreur inconnue'));

                    // Si la transaction a response_code = '00' dans gateway_response, la traiter quand même
                    $gatewayResponse = json_decode($transaction->gateway_response, true);
                    if (isset($gatewayResponse['response_code']) && $gatewayResponse['response_code'] === '00') {
                        $this->info("  → Response code '00' détecté, traitement forcé...");

                        $payload = json_decode($transaction->payload, true);
                        $type = $payload['type'] ?? 'deposit';

                        $callbackData = [
                            'response_code' => '00',
                            'description' => 'completed',
                            'transaction_id' => $transaction->reference,
                            'amount' => abs($transaction->amount),
                            'currency' => $transaction->currency,
                            'auto_checked' => true,
                            'forced' => true,
                            'checked_at' => Carbon::now()->toDateTimeString()
                        ];

                        if ($type === 'withdrawal') {
                            $success = $this->paymentService->processWithdrawalCallback($transaction->id, $callbackData);
                        } else {
                            $success = $this->paymentService->processDepositCallback($transaction->id, $callbackData);
                        }

                        if ($success) {
                            $this->info("  ✓ Transaction traitée avec succès\n");
                            $stats['completed']++;
                        } else {
                            $this->error("  ✗ Échec du traitement\n");
                            $stats['errors']++;
                        }
                    } else {
                        $stats['still_pending']++;
                        $this->line("");
                    }
                }

            } catch (\Exception $e) {
                $this->error("  ✗ Erreur: " . $e->getMessage() . "\n");
                $stats['errors']++;

                Log::error('Error checking pending transaction', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Afficher les statistiques
        $this->newLine();
        $this->info("========================================");
        $this->info("  RÉSUMÉ");
        $this->info("========================================");
        $this->line("Vérifiées:      {$stats['checked']}");
        $this->line("Complétées:     {$stats['completed']}");
        $this->line("Échouées:       {$stats['failed']}");
        $this->line("En attente:     {$stats['still_pending']}");
        $this->line("Erreurs:        {$stats['errors']}");
        $this->newLine();

        return 0;
    }
}
