<?php

namespace App\Console\Commands;

use App\Services\CampaignBudgetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessDailyPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:process-daily 
                           {--dry-run : Run without making actual payments}
                           {--limit= : Limit number of assignments to process}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process daily automatic payments for influencers based on validated views';

    protected $campaignBudgetService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CampaignBudgetService $campaignBudgetService)
    {
        parent::__construct();
        $this->campaignBudgetService = $campaignBudgetService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Starting daily payment processing...');
        
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit');
        
        if ($dryRun) {
            $this->warn('⚠️  Running in DRY RUN mode - no actual payments will be made');
        }
        
        try {
            // Start timing
            $startTime = microtime(true);
            
            if ($dryRun) {
                // For dry run, just show what would be processed
                $result = $this->dryRunAnalysis($limit);
            } else {
                // Process actual payments
                $result = $this->campaignBudgetService->processDailyAutomaticPayments();
            }
            
            if ($result['success']) {
                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 2);
                
                $this->info('✅ Payment processing completed successfully!');
                $this->info("⏱️  Processing time: {$duration} seconds");
                
                // Display summary
                $this->displaySummary($result, $dryRun);
                
                // Log errors if any
                if (!empty($result['errors'])) {
                    $this->warn('⚠️  Some payments failed:');
                    foreach ($result['errors'] as $error) {
                        $this->error("   • {$error}");
                    }
                }
                
                return Command::SUCCESS;
            } else {
                $this->error('❌ Payment processing failed: ' . $result['message']);
                return Command::FAILURE;
            }
            
        } catch (\Exception $e) {
            $this->error('❌ An error occurred: ' . $e->getMessage());
            Log::error('Daily payment processing command failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Perform dry run analysis
     */
    protected function dryRunAnalysis($limit)
    {
        $assignments = \App\Models\Assignment::select([
                'assignments.*',
                'tasks.client_id',
                'tasks.budget as campaign_budget',
                'tasks.name as campaign_name',
                'users.firstname',
                'users.lastname'
            ])
            ->join('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->join('users', 'assignments.agent_id', '=', 'users.id')
            ->where('assignments.status', \App\Consts\Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'])
            ->whereNotNull('assignments.vues')
            ->where('assignments.vues', '>', 0)
            ->whereNull('assignments.payment_date')
            ->when($limit, function($query, $limit) {
                return $query->limit($limit);
            })
            ->get();
        
        $totalAmount = 0;
        $processableCount = 0;
        $errors = [];
        
        $this->info("\n📋 Assignments ready for payment:");
        $this->info("┌─────────────────────────────────────────────────────────────────────────┐");
        $this->info("│ Influencer          │ Campaign            │ Views │ Earnings      │ Status │");
        $this->info("├─────────────────────┼─────────────────────┼───────┼───────────────┼────────┤");
        
        foreach ($assignments as $assignment) {
            $earningsPerView = 3;
            $totalEarnings = $assignment->vues * $earningsPerView;
            $platformCommission = $totalEarnings * 0.1;
            $influencerEarnings = $totalEarnings - $platformCommission;
            
            // Check campaign budget
            $totalSpentOnCampaign = \App\Models\Assignment::where('task_id', $assignment->task_id)
                ->where('status', \App\Consts\Util::ASSIGNMENTS_STATUSES['PAID'])
                ->sum('gain');
                
            $influencerName = substr($assignment->firstname . ' ' . $assignment->lastname, 0, 18);
            $campaignName = substr($assignment->campaign_name, 0, 18);
            
            if (($totalSpentOnCampaign + $totalEarnings) <= $assignment->campaign_budget) {
                $status = "✅ OK";
                $processableCount++;
                $totalAmount += $influencerEarnings;
            } else {
                $status = "❌ No Budget";
                $errors[] = "Assignment {$assignment->id}: Budget insuffisant";
            }
            
            $this->info(sprintf(
                "│ %-19s │ %-19s │ %5d │ %8.0f FCFA │ %-6s │",
                $influencerName,
                $campaignName,
                $assignment->vues,
                $influencerEarnings,
                $status
            ));
        }
        
        $this->info("└─────────────────────────────────────────────────────────────────────────┘");
        
        return [
            'success' => true,
            'processed_payments' => $processableCount,
            'failed_payments' => count($errors),
            'total_amount_paid' => $totalAmount,
            'errors' => $errors
        ];
    }
    
    /**
     * Display payment summary
     */
    protected function displaySummary($result, $dryRun = false)
    {
        $this->info("\n📊 Payment Summary:");
        $this->info("┌─────────────────────────────────────┐");
        
        if ($dryRun) {
            $this->info("│ WOULD BE PROCESSED:                 │");
        } else {
            $this->info("│ PROCESSED:                          │");
        }
        
        $this->info("├─────────────────────────────────────┤");
        $this->info(sprintf("│ ✅ Successful payments: %10d │", $result['processed_payments']));
        $this->info(sprintf("│ ❌ Failed payments:     %10d │", $result['failed_payments']));
        $this->info(sprintf("│ 💰 Total amount:        %10.0f │", $result['total_amount_paid']));
        $this->info("└─────────────────────────────────────┘");
        
        if ($result['processed_payments'] > 0) {
            $avgPayment = $result['total_amount_paid'] / $result['processed_payments'];
            $this->info("📈 Average payment: " . number_format($avgPayment, 0) . " FCFA");
        }
        
        if ($dryRun && $result['processed_payments'] > 0) {
            $this->info("\n💡 To execute these payments, run:");
            $this->info("   php artisan payments:process-daily");
        }
    }
    
    /**
     * Get payments summary for the last 7 days
     */
    protected function getWeeklySummary()
    {
        $weekAgo = \Carbon\Carbon::now()->subDays(7);
        
        $weeklyPayments = \App\Models\Assignment::where('payment_date', '>=', $weekAgo)
            ->where('status', \App\Consts\Util::ASSIGNMENTS_STATUSES['PAID'])
            ->count();
            
        $weeklyAmount = \App\Models\Assignment::where('payment_date', '>=', $weekAgo)
            ->where('status', \App\Consts\Util::ASSIGNMENTS_STATUSES['PAID'])
            ->sum('gain');
        
        return [
            'payments_count' => $weeklyPayments,
            'total_amount' => $weeklyAmount
        ];
    }
    
    /**
     * Display weekly summary
     */
    protected function displayWeeklySummary()
    {
        $summary = $this->getWeeklySummary();
        
        $this->info("\n📅 Last 7 days summary:");
        $this->info("   Payments processed: " . $summary['payments_count']);
        $this->info("   Total amount paid: " . number_format($summary['total_amount'], 0) . " FCFA");
    }
}