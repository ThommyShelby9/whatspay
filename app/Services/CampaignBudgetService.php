<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Task;
use App\Models\Assignment;
use App\Models\Transaction;
use App\Models\User;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CampaignBudgetService
{
    use Utils;
    
    protected $walletService;
    protected $paymentService;
    
    public function __construct(WalletService $walletService, PaymentService $paymentService)
    {
        $this->walletService = $walletService;
        $this->paymentService = $paymentService;
    }
    
    /**
     * Reserve budget for a campaign when it's created
     *
     * @param string $taskId Campaign ID
     * @param string $clientId Client ID
     * @param float $budget Campaign budget
     * @return array Result
     */
    public function reserveCampaignBudget($taskId, $clientId, $budget)
    {
        try {
            DB::beginTransaction();
            
            // Check if client has sufficient balance
            $clientBalance = $this->walletService->getBalance($clientId);
            
            if ($clientBalance < $budget) {
                return [
                    'success' => false,
                    'message' => 'Solde insuffisant. Veuillez recharger votre compte.',
                    'required' => $budget,
                    'available' => $clientBalance,
                    'missing' => $budget - $clientBalance
                ];
            }
            
            // Deduct budget from client's wallet (reserve it)
            $deductResult = $this->walletService->deductFunds(
                $clientId,
                $budget,
                "Réservation budget campagne #{$taskId}"
            );
            
            if (!$deductResult['success']) {
                DB::rollBack();
                return $deductResult;
            }
            
            // Update task status to indicate budget is reserved
            Task::where('id', $taskId)->update([
                'status' => Util::TASKS_STATUSES['PENDING'],
                'budget_reserved_at' => Carbon::now()
            ]);
            
            // Create a budget reservation transaction
            Transaction::create([
                'id' => $this->getId(),
                'user_id' => $clientId,
                'wallet_id' => $this->walletService->getWalletId($clientId),
                'amount' => $budget,
                'type' => 'BUDGET_RESERVE',
                'status' => 'COMPLETED',
                'description' => "Réservation budget pour campagne: {$taskId}",
                'transaction_id' => 'BDG-' . time() . '-' . $taskId,
                'receipt_url' => null,
                'related_id' => $taskId
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Budget réservé avec succès',
                'reserved_amount' => $budget,
                'remaining_balance' => $clientBalance - $budget
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Campaign budget reservation error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la réservation du budget'
            ];
        }
    }
    
    /**
     * Release reserved budget when campaign is rejected or cancelled
     *
     * @param string $taskId Campaign ID
     * @return array Result
     */
    public function releaseCampaignBudget($taskId)
    {
        try {
            DB::beginTransaction();
            
            $task = Task::find($taskId);
            
            if (!$task) {
                return [
                    'success' => false,
                    'message' => 'Campagne non trouvée'
                ];
            }
            
            // Add budget back to client's wallet
            $addResult = $this->walletService->addFunds(
                $task->client_id,
                $task->budget,
                $this->getId(),
                "Libération budget campagne rejetée #{$taskId}"
            );
            
            if (!$addResult) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la libération du budget'
                ];
            }
            
            // Update task status
            Task::where('id', $taskId)->update([
                'budget_released_at' => Carbon::now()
            ]);
            
            // Create budget release transaction
            Transaction::create([
                'id' => $this->getId(),
                'user_id' => $task->client_id,
                'wallet_id' => $this->walletService->getWalletId($task->client_id),
                'amount' => $task->budget,
                'type' => 'BUDGET_RELEASE',
                'status' => 'COMPLETED',
                'description' => "Libération budget campagne rejetée: {$taskId}",
                'transaction_id' => 'REL-' . time() . '-' . $taskId,
                'receipt_url' => null,
                'related_id' => $taskId
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Budget libéré avec succès',
                'released_amount' => $task->budget
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Campaign budget release error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la libération du budget'
            ];
        }
    }
    
    /**
     * Calculate and pay influencers based on validated views
     * Rate: 3 FCFA per view
     *
     * @param string $assignmentId Assignment ID
     * @param int $validatedViews Number of validated views
     * @return array Result
     */
    public function payInfluencerForViews($assignmentId, $validatedViews)
    {
        try {
            DB::beginTransaction();
            
            $assignment = Assignment::select([
                'assignments.*',
                'tasks.client_id',
                'tasks.budget as campaign_budget',
                'tasks.name as campaign_name'
            ])
            ->join('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->where('assignments.id', $assignmentId)
            ->first();
            
            if (!$assignment) {
                return [
                    'success' => false,
                    'message' => 'Assignment non trouvée'
                ];
            }
            
            // Calculate earnings (3 FCFA per view)
            $earningsPerView = 3;
            $totalEarnings = $validatedViews * $earningsPerView;
            $platformCommission = $totalEarnings * 0.1; // 10% commission
            $influencerEarnings = $totalEarnings - $platformCommission;
            
            // Check if there's enough campaign budget left
            $totalSpentOnCampaign = Assignment::where('task_id', $assignment->task_id)
                ->where('status', Util::ASSIGNMENTS_STATUSES['PAID'])
                ->sum('gain');
                
            if (($totalSpentOnCampaign + $totalEarnings) > $assignment->campaign_budget) {
                return [
                    'success' => false,
                    'message' => 'Budget de campagne insuffisant pour ce paiement',
                    'required' => $totalEarnings,
                    'available' => $assignment->campaign_budget - $totalSpentOnCampaign
                ];
            }
            
            // Update assignment with earnings
            Assignment::where('id', $assignmentId)->update([
                'vues' => $validatedViews,
                'gain' => $influencerEarnings,
                'status' => Util::ASSIGNMENTS_STATUSES['PAID'],
                'payment_date' => Carbon::now()
            ]);
            
            // Add earnings to influencer's wallet
            $this->walletService->addFunds(
                $assignment->agent_id,
                $influencerEarnings,
                $this->getId(),
                "Gains campagne: {$assignment->campaign_name} ({$validatedViews} vues)"
            );
            
            // Record platform commission
            Transaction::create([
                'id' => $this->getId(),
                'user_id' => null, // Platform transaction
                'wallet_id' => null,
                'amount' => $platformCommission,
                'type' => 'PLATFORM_COMMISSION',
                'status' => 'COMPLETED',
                'description' => "Commission plateforme - Assignment {$assignmentId}",
                'transaction_id' => 'COM-' . time() . '-' . $assignmentId,
                'receipt_url' => null,
                'related_id' => $assignmentId
            ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Paiement effectué avec succès',
                'influencer_earnings' => $influencerEarnings,
                'platform_commission' => $platformCommission,
                'views_count' => $validatedViews,
                'rate_per_view' => $earningsPerView
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Influencer payment error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors du paiement'
            ];
        }
    }
    
    /**
     * Process automatic daily payments for completed assignments
     * This method should be called by a scheduled job
     *
     * @return array Summary of processed payments
     */
    public function processDailyAutomaticPayments()
    {
        try {
            $processedPayments = 0;
            $totalAmountPaid = 0;
            $failedPayments = 0;
            $errors = [];
            
            // Get all completed assignments that haven't been paid yet
            $completedAssignments = Assignment::select([
                'assignments.*',
                'tasks.client_id',
                'tasks.budget as campaign_budget',
                'tasks.name as campaign_name'
            ])
            ->join('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->where('assignments.status', Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'])
            ->whereNotNull('assignments.vues')
            ->where('assignments.vues', '>', 0)
            ->whereNull('assignments.payment_date')
            ->get();
            
            foreach ($completedAssignments as $assignment) {
                $result = $this->payInfluencerForViews(
                    $assignment->id,
                    $assignment->vues
                );
                
                if ($result['success']) {
                    $processedPayments++;
                    $totalAmountPaid += $result['influencer_earnings'];
                } else {
                    $failedPayments++;
                    $errors[] = "Assignment {$assignment->id}: {$result['message']}";
                }
            }
            
            // Log the daily payment processing
            Log::info('Daily automatic payments processed', [
                'processed' => $processedPayments,
                'failed' => $failedPayments,
                'total_amount' => $totalAmountPaid,
                'errors' => $errors
            ]);
            
            return [
                'success' => true,
                'processed_payments' => $processedPayments,
                'failed_payments' => $failedPayments,
                'total_amount_paid' => $totalAmountPaid,
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            Log::error('Daily payment processing error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement automatique des paiements'
            ];
        }
    }
    
    /**
     * Get campaign budget statistics
     *
     * @param string $taskId Campaign ID
     * @return array Budget statistics
     */
    public function getCampaignBudgetStats($taskId)
    {
        try {
            $task = Task::find($taskId);
            
            if (!$task) {
                return [
                    'success' => false,
                    'message' => 'Campagne non trouvée'
                ];
            }
            
            // Calculate total spent
            $totalSpent = Assignment::where('task_id', $taskId)
                ->where('status', Util::ASSIGNMENTS_STATUSES['PAID'])
                ->sum('gain');
            
            // Calculate pending payments (accepted submissions)
            $pendingPayments = Assignment::where('task_id', $taskId)
                ->where('status', Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'])
                ->whereNotNull('vues')
                ->where('vues', '>', 0)
                ->sum(DB::raw('vues * 3 * 0.9')); // 3 FCFA per view minus 10% commission
            
            // Calculate total views
            $totalViews = Assignment::where('task_id', $taskId)
                ->where('status', Util::ASSIGNMENTS_STATUSES['PAID'])
                ->sum('vues');
            
            $pendingViews = Assignment::where('task_id', $taskId)
                ->where('status', Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'])
                ->whereNotNull('vues')
                ->sum('vues');
            
            return [
                'success' => true,
                'campaign' => [
                    'id' => $task->id,
                    'name' => $task->name,
                    'total_budget' => $task->budget,
                    'budget_spent' => $totalSpent,
                    'budget_pending' => $pendingPayments,
                    'budget_remaining' => $task->budget - $totalSpent - $pendingPayments,
                    'total_views' => $totalViews,
                    'pending_views' => $pendingViews,
                    'budget_utilization' => ($totalSpent / $task->budget) * 100
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Campaign budget stats error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques'
            ];
        }
    }
    
    /**
     * Get client's campaign spending overview
     *
     * @param string $clientId Client ID
     * @return array Spending overview
     */
    public function getClientSpendingOverview($clientId)
    {
        try {
            // Get all client campaigns
            $campaigns = Task::where('client_id', $clientId)->get();
            
            $totalBudgetReserved = $campaigns->sum('budget');
            
            // Calculate total spent
            $totalSpent = Assignment::join('tasks', 'assignments.task_id', '=', 'tasks.id')
                ->where('tasks.client_id', $clientId)
                ->where('assignments.status', Util::ASSIGNMENTS_STATUSES['PAID'])
                ->sum('assignments.gain');
            
            // Calculate pending payments
            $pendingPayments = Assignment::join('tasks', 'assignments.task_id', '=', 'tasks.id')
                ->where('tasks.client_id', $clientId)
                ->where('assignments.status', Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'])
                ->whereNotNull('assignments.vues')
                ->where('assignments.vues', '>', 0)
                ->sum(DB::raw('assignments.vues * 3 * 0.9'));
            
            // Get current wallet balance
            $currentBalance = $this->walletService->getBalance($clientId);
            
            return [
                'success' => true,
                'overview' => [
                    'total_campaigns' => $campaigns->count(),
                    'active_campaigns' => $campaigns->where('status', Util::TASKS_STATUSES['ACCEPTED'])->count(),
                    'total_budget_reserved' => $totalBudgetReserved,
                    'total_spent' => $totalSpent,
                    'pending_payments' => $pendingPayments,
                    'current_wallet_balance' => $currentBalance,
                    'total_available' => $currentBalance + ($totalBudgetReserved - $totalSpent - $pendingPayments)
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Client spending overview error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors du calcul de l\'aperçu des dépenses'
            ];
        }
    }
}