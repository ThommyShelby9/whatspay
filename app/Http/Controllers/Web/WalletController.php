<?php
// File: app/Http/Controllers/Web/WalletController.php (Version complète corrigée)

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Services\PaymentService;
use App\Services\CampaignBudgetService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    use Utils;

    protected $walletService;
    protected $paymentService;
    protected $campaignBudgetService;

    public function __construct(
        WalletService $walletService,
        PaymentService $paymentService,
        CampaignBudgetService $campaignBudgetService
    ) {
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
        $this->campaignBudgetService = $campaignBudgetService;
    }

    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        try {
            // Récupérer le solde du portefeuille
            $viewData['balance'] = $this->walletService->getBalance($userId) ?? 0;

            // Récupérer les transactions récentes (15 dernières)
            $viewData['transactions'] = $this->walletService->getTransactions($userId, 15) ?? collect([]);

            // Récupérer les méthodes de paiement disponibles
            try {
                $viewData['paymentMethods'] = $this->paymentService->getAvailablePaymentMethods() ?? collect([]);
            } catch (\Exception $e) {
                Log::warning('Could not fetch payment methods: ' . $e->getMessage());
                $viewData['paymentMethods'] = collect([]);
            }

            // Récupérer les statistiques de dépenses du client
            try {
                $spendingOverview = $this->campaignBudgetService->getClientSpendingOverview($userId);
                if ($spendingOverview['success']) {
                    $viewData['spendingStats'] = $spendingOverview['overview'];
                } else {
                    $viewData['spendingStats'] = $this->getDefaultSpendingStats();
                }
            } catch (\Exception $e) {
                Log::warning('Could not fetch spending stats: ' . $e->getMessage());
                $viewData['spendingStats'] = $this->getDefaultSpendingStats();
            }

            // Récupérer les statistiques de transactions
            try {
                $viewData['transactionStats'] = $this->walletService->getTransactionStats($userId);
            } catch (\Exception $e) {
                Log::warning('Could not fetch transaction stats: ' . $e->getMessage());
                $viewData['transactionStats'] = $this->getDefaultTransactionStats();
            }

            // Vérifier s'il y a des alertes de solde faible
            $viewData['lowBalanceAlert'] = $this->checkLowBalanceAlert($userId);
        } catch (\Exception $e) {
            Log::error('Error in wallet index: ' . $e->getMessage());

            // Données par défaut en cas d'erreur
            $viewData['balance'] = 0;
            $viewData['transactions'] = collect([]);
            $viewData['paymentMethods'] = collect([]);
            $viewData['spendingStats'] = $this->getDefaultSpendingStats();
            $viewData['transactionStats'] = $this->getDefaultTransactionStats();
            $viewData['lowBalanceAlert'] = null;

            // Ajouter un message d'erreur
            $alert = [
                'type' => 'warning',
                'message' => 'Certaines données n\'ont pas pu être chargées. Veuillez actualiser la page.'
            ];
        }

        $this->setViewData($request, $viewData);

        return view('annonceur.wallet.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Portefeuille',
            'pagetilte' => 'Portefeuille',
            'pagecardtilte' => 'Gérer votre budget et vos paiements'
        ]);
    }

    public function addFunds(Request $request)
    {
        try {
            $userId = $request->session()->get('userid');

            Log::info('=== DÉBUT DEPOT ===', [
                'user_id' => $userId,
                'request_data' => $request->all()
            ]);

            // Validation des données
            $request->validate([
                'payment_method' => 'required|string',
                'amount' => 'required|numeric|min:1|max:1000000',
                'phone' => 'required|string|regex:/^[0-9+]{8,15}$/',
            ], [
                'amount.min' => 'Le montant minimum est de 1 FCFA',
                'amount.max' => 'Le montant maximum est de 1 000 000 FCFA',
                'phone.required' => 'Le numéro de téléphone est requis',
                'phone.regex' => 'Format de numéro de téléphone invalide',
            ]);

            Log::info('Validation OK');

            // Nettoyer le numéro de téléphone (le PaymentService se chargera du formatage)
            $phone = preg_replace('/[^0-9+]/', '', $request->input('phone'));

            Log::info('Téléphone nettoyé', ['phone' => $phone]);

            // Vérifier si PaymentService existe
            if (!$this->paymentService) {
                Log::error('PaymentService non initialisé');
                throw new \Exception('Service de paiement non disponible');
            }

            Log::info('PaymentService OK, appel initiateDeposit...');

            // Initier le dépôt via PayPlus
            $result = $this->paymentService->initiateDeposit(
                $userId,
                $request->input('amount'),
                $phone,
                true
            );

            Log::info('Résultat PaymentService', $result);

            if ($result['success']) {
                session([
                    'pending_deposit' => [
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $request->input('amount'),
                        'phone' => $phone
                    ]
                ]);

                Log::info('Redirection vers PayPlus', ['url' => $result['redirect_url']]);

                return redirect()->away($result['redirect_url']);
            } else {
                Log::warning('Échec PaymentService', $result);

                return redirect()->route('announcer.wallet')
                    ->with('type', 'danger')
                    ->with('message', $result['message']);
            }
        } catch (ValidationException $e) {
            Log::error('Erreur validation', $e->errors());

            return redirect()->route('announcer.wallet')
                ->withErrors($e->errors())
                ->withInput()
                ->with('type', 'danger')
                ->with('message', 'Veuillez corriger les erreurs dans le formulaire');
        } catch (\Exception $e) {
            Log::error('ERREUR GENERALE DEPOT', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // En mode développement, afficher l'erreur réelle
            $errorMessage = config('app.debug') ?
                'ERREUR DEBUG: ' . $e->getMessage() . ' (Ligne ' . $e->getLine() . ')' :
                'Une erreur est survenue. Veuillez réessayer.';

            return redirect()->route('announcer.wallet')
                ->with('type', 'danger')
                ->with('message', $errorMessage);
        }
    }

    /**
     * Traiter le retour après paiement PayPlus
     */
    public function handlePaymentReturn(Request $request)
    {
        Log::info('=== RETOUR PAIEMENT PAYPLUS ===', [
            'query_params' => $request->query(),
            'all_params' => $request->all()
        ]);

        $status = $request->get('status');
        $pendingDeposit = session('pending_deposit');

        Log::info('Status retour et session', [
            'status' => $status,
            'pending_deposit' => $pendingDeposit
        ]);

        if ($pendingDeposit) {
            session()->forget('pending_deposit');

            if ($status === 'success') {
                // Vérifier le statut de la transaction avec PayPlus
                $transactionStatus = $this->paymentService->checkTransactionStatus($pendingDeposit['transaction_id']);

                Log::info('Vérification statut transaction', [
                    'transaction_id' => $pendingDeposit['transaction_id'],
                    'status_check_result' => $transactionStatus
                ]);

                if ($transactionStatus['success'] && $transactionStatus['status'] === 'completed') {
                    return redirect()->route('announcer.wallet')
                        ->with('type', 'success')
                        ->with('message', 'Votre dépôt de ' . number_format($pendingDeposit['amount'], 0, ',', ' ') . ' FCFA a été effectué avec succès !');
                } else {
                    return redirect()->route('announcer.wallet')
                        ->with('type', 'warning')
                        ->with('message', 'Votre paiement est en cours de traitement. Vous recevrez une confirmation sous peu.');
                }
            } else {
                return redirect()->route('announcer.wallet')
                    ->with('type', 'danger')
                    ->with('message', 'Votre paiement a été annulé ou a échoué.');
            }
        }

        return redirect()->route('announcer.wallet');
    }

    /**
     * Afficher l'historique complet des transactions
     */
    public function transactionHistory(Request $request)
    {
        $userId = $request->session()->get('userid');

        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        try {
            $viewData['transactions'] = $this->walletService->getPaginatedTransactions($userId, 25);
            $viewData['transactionStats'] = $this->walletService->getTransactionStats($userId);
            $viewData['balance'] = $this->walletService->getBalance($userId);
        } catch (\Exception $e) {
            Log::error('Error in transaction history: ' . $e->getMessage());

            $viewData['transactions'] = collect([]);
            $viewData['transactionStats'] = $this->getDefaultTransactionStats();
            $viewData['balance'] = 0;

            $alert = [
                'type' => 'warning',
                'message' => 'Impossible de charger l\'historique. Veuillez réessayer.'
            ];
        }

        $this->setViewData($request, $viewData);

        return view('annonceur.wallet.history', [
            'alert' => $alert,
            'viewData' => $viewData,
            'title' => 'WhatsPAY | Historique des transactions',
            'pagetilte' => 'Historique des transactions',
            'pagecardtilte' => 'Toutes vos transactions'
        ]);
    }

    /**
     * API endpoint pour récupérer les statistiques de portefeuille
     */
    public function getWalletStats(Request $request)
    {
        $userId = $request->session()->get('userid');

        try {
            $summary = $this->walletService->getWalletSummary($userId);

            try {
                $spendingOverview = $this->campaignBudgetService->getClientSpendingOverview($userId);
                $spending = $spendingOverview['success'] ? $spendingOverview['overview'] : null;
            } catch (\Exception $e) {
                $spending = null;
                Log::warning('Could not fetch spending overview for API: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'wallet' => $summary,
                'spending' => $spending
            ]);
        } catch (\Exception $e) {
            Log::error('Error in wallet stats API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques'
            ], 500);
        }
    }

    /**
     * Initier un retrait (pour les influenceurs)
     */
    public function initiateWithdrawal(Request $request)
    {
        try {
            $userId = $request->session()->get('userid');

            // Validation
            $request->validate([
                'amount' => 'required|numeric|min:500|max:500000',
                'withdrawal_method' => 'required|in:mobile_money,bank',
                'phone' => 'required_if:withdrawal_method,mobile_money|string|regex:/^[0-9]{8,15}$/',
                'bank_account' => 'required_if:withdrawal_method,bank|string',
                'bank_name' => 'required_if:withdrawal_method,bank|string',
            ], [
                'amount.min' => 'Le montant minimum de retrait est de 500 FCFA',
                'amount.max' => 'Le montant maximum de retrait est de 500 000 FCFA',
                'phone.required_if' => 'Le numéro de téléphone est requis pour Mobile Money',
                'phone.regex' => 'Format de numéro de téléphone invalide',
                'bank_account.required_if' => 'Le numéro de compte est requis',
                'bank_name.required_if' => 'Le nom de la banque est requis',
            ]);

            $amount = $request->input('amount');
            $balance = $this->walletService->getBalance($userId);

            // Vérifier le solde disponible
            if ($balance < $amount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solde insuffisant. Solde disponible : ' . number_format($balance, 0, ',', ' ') . ' FCFA'
                ]);
            }

            // Traitement selon la méthode de retrait
            if ($request->input('withdrawal_method') === 'mobile_money') {
                // Nettoyer le numéro de téléphone (le PaymentService se chargera du formatage avec 229)
                $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));

                // Initier le retrait via PayPlus
                $result = $this->paymentService->initiateWithdrawal(
                    $userId,
                    $amount,
                    $phone,
                    false // Direct mobile money
                );
            } else {
                // Pour les retraits bancaires, créer une demande manuelle
                $result = $this->createBankWithdrawalRequest($userId, $amount, $request->all());
            }

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Withdrawal error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ?
                    'Erreur: ' . $e->getMessage() :
                    'Une erreur est survenue. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Créer une demande de retrait bancaire
     */
    private function createBankWithdrawalRequest($userId, $amount, $bankData)
    {
        try {
            $transactionId = $this->getId();

            // Créer une transaction en attente pour retrait bancaire
            \App\Models\PaymentTransaction::create([
                'id' => $transactionId,
                'user_id' => $userId,
                'amount' => -$amount,
                'currency' => 'XOF',
                'status' => 'PENDING',
                'reference' => 'BANK-' . time() . '-' . substr($transactionId, 0, 8),
                'payload' => json_encode([
                    'type' => 'bank_withdrawal',
                    'bank_name' => $bankData['bank_name'],
                    'account_number' => $bankData['bank_account'],
                    'account_holder' => $bankData['account_holder'] ?? '',
                    'amount' => $amount
                ])
            ]);

            // Déduire temporairement les fonds (seront remboursés si le retrait échoue)
            $deductResult = $this->walletService->deductFunds(
                $userId,
                $amount,
                'Demande de retrait bancaire - ' . $bankData['bank_name'],
                $transactionId
            );

            if ($deductResult['success']) {
                return [
                    'success' => true,
                    'message' => 'Votre demande de retrait bancaire a été soumise. Elle sera traitée dans 2-3 jours ouvrables.',
                    'transaction_id' => $transactionId
                ];
            } else {
                return $deductResult;
            }
        } catch (\Exception $e) {
            Log::error('Bank withdrawal request error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la demande de retrait'
            ];
        }
    }

    /**
     * Get default spending stats
     */
    private function getDefaultSpendingStats()
    {
        return [
            'total_campaigns' => 0,
            'active_campaigns' => 0,
            'total_budget_reserved' => 0,
            'total_spent' => 0,
            'pending_payments' => 0,
            'current_wallet_balance' => 0,
            'total_available' => 0
        ];
    }

    /**
     * Get default transaction stats
     */
    private function getDefaultTransactionStats()
    {
        return [
            'total_transactions' => 0,
            'total_credits' => 0,
            'total_debits' => 0,
            'this_month_credits' => 0,
            'this_month_debits' => 0,
            'last_transaction_date' => null
        ];
    }

    /**
     * Vérifier les alertes de solde faible
     */
    private function checkLowBalanceAlert($userId)
    {
        $balance = $this->walletService->getBalance($userId);

        try {
            $spendingOverview = $this->campaignBudgetService->getClientSpendingOverview($userId);

            if ($spendingOverview['success']) {
                $pendingPayments = $spendingOverview['overview']['pending_payments'] ?? 0;

                if ($balance < $pendingPayments) {
                    return [
                        'type' => 'danger',
                        'message' => 'Votre solde est insuffisant pour couvrir les paiements en attente (' . number_format($pendingPayments, 0, ',', ' ') . ' FCFA)',
                        'action_required' => true
                    ];
                }

                if ($balance < 10000 && $balance > 0) {
                    return [
                        'type' => 'warning',
                        'message' => 'Votre solde est faible (' . number_format($balance, 0, ',', ' ') . ' FCFA). Pensez à le recharger.',
                        'action_required' => false
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('Could not check balance alerts: ' . $e->getMessage());
        }

        return null;
    }

    private function setAlert(Request &$request, &$alert)
    {
        $alert = [
            'message' => (!empty($request->message) ? $request->message : (!empty(session('message')) ? session('message') : "")),
            'type' => (!empty($request->type) ? $request->type : (!empty(session('type')) ? session('type') : "success")),
        ];
    }

    private function setViewData(Request &$request, &$viewData)
    {
        $viewData['uri'] = \Route::currentRouteName();
        $viewData['baseUrl'] = config('app.url');
        $viewData['version'] = gmdate('YmdHis');
        $viewData['user'] = ($request->session()->has('user') ? $request->session()->get('user') : "");
        $viewData['userid'] = ($request->session()->has('userid') ? $request->session()->get('userid') : "");
        $viewData['userprofile'] = ($request->session()->has('userprofile') ? $request->session()->get('userprofile') : "");
        $viewData['userrights'] = ($request->session()->has('userrights') ? (json_decode($request->session()->get('userrights'), true)) : []);
        $viewData['userfirstname'] = ($request->session()->has('userfirstname') ? $request->session()->get('userfirstname') : "");
        $viewData['userlastname'] = ($request->session()->has('userlastname') ? $request->session()->get('userlastname') : "");
    }
}
