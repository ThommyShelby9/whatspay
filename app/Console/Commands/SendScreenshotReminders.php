<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendScreenshotReminders extends Command
{
    protected $signature = 'whatspay:send-screenshot-reminders';
    protected $description = 'Send WhatsApp reminders for campaign screenshots after 23.5 hours';

    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        parent::__construct();
        $this->whatsAppService = $whatsAppService;
    }

    public function handle()
    {
        // Find assignments that are ~23.5 hours old and don't have screenshots yet
        $timeThreshold = Carbon::now()->subHours(23)->subMinutes(30);
        
        $assignments = Assignment::where('created_at', '<=', $timeThreshold)
            ->where('created_at', '>=', Carbon::now()->subHours(24)) // Only from the last 24 hours
            ->where('status', 'ACCEPTED')
            ->where(function($query) {
                $query->where('screenshot', null)
                      ->orWhere('screenshot', '');
            })
            ->with(['user', 'campaign'])
            ->get();
            
        $this->info("Found {$assignments->count()} assignments needing screenshot reminders");
        
        $successCount = 0;
        $failedCount = 0;
        
        foreach ($assignments as $assignment) {
            $result = $this->whatsAppService->sendCampaignScreenshotReminder($assignment);
            
            if ($result['success']) {
                $successCount++;
                $this->info("✅ Reminder sent for assignment #{$assignment->id} to {$assignment->user->firstname} {$assignment->user->lastname}");
            } else {
                $failedCount++;
                $this->error("❌ Failed to send reminder for assignment #{$assignment->id} to {$assignment->user->firstname} {$assignment->user->lastname}");
            }
        }
        
        $this->info("Reminder process completed: {$successCount} successful, {$failedCount} failed");
        
        return Command::SUCCESS;
    }
}