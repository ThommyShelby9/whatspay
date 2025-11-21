<?php
// File: app/Http/Controllers/Web/Influencer/EarningController.php (PostgreSQL Fix)

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\AssignmentService;
use App\Services\WalletService;
use App\Services\PaymentService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EarningController extends Controller
{
    use Utils;

    protected $assignmentService;
    protected $walletService;
    protected $paymentService;

    public function __construct(
        AssignmentService $assignmentService,
        WalletService $walletService,
        PaymentService $paymentService
    ) {
        $this->assignmentService = $assignmentService;
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer les statistiques de gains
        try {
            $earningsStats = $this->assignmentService->getAgentEarningsStats($userId);
        } catch (\Exception $e) {
            $earningsStats = [
                'total_gain' => 0,
                'this_month' => 0,
                'last_month' => 0,
                'monthly_average' => 0
            ];
        }

        // Ajouter le solde du wallet et les paiements en attente
        $viewData['balance'] = $this->walletService->getBalance($userId);
        $pendingEarnings = $this->calculatePendingEarnings($userId);

        $viewData["earningsStats"] = [
            'total_earnings' => $earningsStats['total_gain'],
            'current_month' => $earningsStats['this_month'],
            'last_month' => $earningsStats['last_month'],
            'monthly_average' => $earningsStats['monthly_average'],
            'pending_payment' => $pendingEarnings,
            'available_for_withdrawal' => $viewData['balance']
        ];


        // Récupérer l'historique des transactions de gains
        $viewData['earningsHistory'] = $this->getEarningsHistory($userId);

        // Récupérer les données pour le graphique (12 derniers mois)
        $viewData['chartData'] = $this->getChartData($userId);

        // Récupérer les statistiques d'assignments
        try {
            $viewData['assignmentStats'] = $this->assignmentService->getAgentAssignmentStats($userId);
        } catch (\Exception $e) {
            $viewData['assignmentStats'] = [
                'completed' => 0,
                'total_vues' => 0,
                'pending' => 0
            ];
        }

        // Calculer les prochaines dates de paiement
        $viewData['nextPaymentDate'] = $this->getNextPaymentDate();

        $this->setViewData($request, $viewData);

        return view('influencer.earnings.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Mes Gains',
            'pagetilte' => 'Mes Gains',
            'pagecardtilte' => 'Historique des revenus'
        ]);
    }

    /**
     * Récupérer les données pour le graphique des gains (PostgreSQL compatible)
     */
    public function getChartData($userId, $period = '12months')
    {
        $endDate = Carbon::now();

        switch ($period) {
            case '6months':
                $startDate = $endDate->copy()->subMonths(6);
                $format = 'Y-m';
                break;
            case 'year':
                $startDate = $endDate->copy()->subYear();
                $format = 'Y-m';
                break;
            case 'all':
                $startDate = Carbon::parse('2023-01-01');
                $format = 'Y-m';
                break;
            default:
                $startDate = $endDate->copy()->subYear();
                $format = 'Y-m';
        }

        try {
            // Requête PostgreSQL compatible avec TO_CHAR au lieu de DATE_FORMAT
            $earnings = DB::table('assignments')
                ->selectRaw("TO_CHAR(payment_date, 'YYYY-MM') as month, SUM(gain) as total_gain")
                ->where('agent_id', $userId)
                ->where('status', \App\Consts\Util::ASSIGNMENTS_STATUSES['PAID'])
                ->whereNotNull('payment_date')
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->groupBy(DB::raw("TO_CHAR(payment_date, 'YYYY-MM')"))
                ->orderBy('month')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Chart data error: ' . $e->getMessage());

            // Fallback avec des données vides en cas d'erreur
            $earnings = collect([]);
        }

        // Préparer les données pour le graphique
        $categories = [];
        $data = [];

        $current = $startDate->copy();
        while ($current <= $endDate) {
            $monthKey = $current->format($format);
            $monthLabel = $current->format('M Y');

            $categories[] = $monthLabel;

            $monthEarnings = $earnings->firstWhere('month', $monthKey);
            $data[] = $monthEarnings ? (float)$monthEarnings->total_gain : 0;

            $current->addMonth();
        }

        return [
            'categories' => $categories,
            'data' => $data
        ];
    }

    /**
     * API endpoint pour les données du graphique
     */
    public function apiChartData(Request $request)
    {
        $userId = $request->session()->get('userid');
        $period = $request->get('period', '12months');

        try {
            $chartData = $this->getChartData($userId, $period);

            return response()->json([
                'success' => true,
                'data' => $chartData
            ]);
        } catch (\Exception $e) {
            \Log::error('API Chart data error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des données',
                'data' => [
                    'categories' => [],
                    'data' => []
                ]
            ]);
        }
    }

    /**
     * Traiter une demande de retrait
     */
    public function requestWithdrawal(Request $request)
    {
        try {
            $userId = $request->session()->get('userid');

            // Validation des données
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
                    false
                );
            } else {
                // Pour les retraits bancaires, créer une demande manuelle
                $result = $this->createBankWithdrawalRequest($userId, $amount, $request->all());
            }

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'transaction_id' => $result['transaction_id'] ?? null
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Withdrawal error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Calculer les gains en attente de paiement
     */
    private function calculatePendingEarnings($userId)
    {
        try {
            return DB::table('assignments')
                ->where('agent_id', $userId)
                ->where('status', \App\Consts\Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'])
                ->whereNotNull('vues')
                ->where('vues', '>', 0)
                ->whereNull('payment_date')
                ->sum(DB::raw('vues * 3 * 0.9')); // 3 FCFA per view minus 10% commission
        } catch (\Exception $e) {
            \Log::error('Calculate pending earnings error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupérer l'historique des transactions de gains
     */
    private function getEarningsHistory($userId)
    {
        return $this->walletService->getTransactions($userId, 20);
    }

    /**
     * Calculer la prochaine date de paiement
     */
    private function getNextPaymentDate()
    {
        $tomorrow = Carbon::tomorrow()->setTime(2, 0);
        return $tomorrow;
    }

    /**
     * Créer une demande de retrait bancaire
     */
    private function createBankWithdrawalRequest($userId, $amount, $bankData)
    {
        try {
            $transactionId = $this->getId();

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

            // Déduire temporairement les fonds
            $deductResult = $this->walletService->deductFunds(
                $userId,
                $amount,
                'Demande de retrait bancaire - ' . $bankData['bank_name']
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
            \Log::error('Bank withdrawal request error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la demande de retrait'
            ];
        }
    }

    /**
     * Exporter les données de gains
     */
    public function exportEarnings(Request $request)
    {
        $userId = $request->session()->get('userid');
        $format = $request->get('format', 'csv');
        $period = $request->get('period', 'all');

        try {
            $data = $this->getExportData($userId, $period);

            if ($format === 'csv') {
                return $this->exportToCsv($data);
            } else {
                return response()->json([
                    'message' => 'Export PDF en cours de développement'
                ], 501);
            }
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());

            return redirect()->back()->with([
                'type' => 'danger',
                'message' => 'Erreur lors de l\'exportation des données'
            ]);
        }
    }

    /**
     * Récupérer les données pour l'export
     */
    private function getExportData($userId, $period)
    {
        try {
            $query = DB::table('assignments')
                ->join('tasks', 'assignments.task_id', '=', 'tasks.id')
                ->select([
                    'assignments.payment_date',
                    'tasks.name as campaign_name',
                    'assignments.vues',
                    'assignments.gain',
                    'assignments.status'
                ])
                ->where('assignments.agent_id', $userId)
                ->where('assignments.status', \App\Consts\Util::ASSIGNMENTS_STATUSES['PAID'])
                ->whereNotNull('assignments.payment_date')
                ->orderBy('assignments.payment_date', 'desc');

            // Appliquer les filtres de période
            if ($period !== 'all') {
                $endDate = Carbon::now();
                switch ($period) {
                    case '3months':
                        $startDate = $endDate->copy()->subMonths(3);
                        break;
                    case '6months':
                        $startDate = $endDate->copy()->subMonths(6);
                        break;
                    case 'year':
                        $startDate = $endDate->copy()->subYear();
                        break;
                    default:
                        $startDate = $endDate->copy()->subMonths(6);
                }

                $query->whereBetween('assignments.payment_date', [$startDate, $endDate]);
            }

            return $query->get();
        } catch (\Exception $e) {
            \Log::error('Export data error: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Exporter en CSV
     */
    private function exportToCsv($data)
    {
        $filename = 'gains-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($data) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, ['Date', 'Campagne', 'Vues', 'Gains (FCFA)', 'Statut']);

            foreach ($data as $row) {
                fputcsv($file, [
                    date('d/m/Y', strtotime($row->payment_date)),
                    $row->campaign_name,
                    $row->vues,
                    $row->gain,
                    'Payé'
                ]);
            }

            fclose($file);
        }, 200, $headers);
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
