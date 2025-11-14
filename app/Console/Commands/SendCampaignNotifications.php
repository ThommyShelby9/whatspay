<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendCampaignNotifications extends Command
{
    protected $signature = 'whatspay:send-campaign-notifications {campaign_id}';
    protected $description = 'Send WhatsApp notifications for a new campaign to all eligible influencers';

    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        parent::__construct();
        $this->whatsAppService = $whatsAppService;
    }

    public function handle()
    {
        $campaignId = $this->argument('campaign_id');
        $campaign = Task::findOrFail($campaignId);
        
        $this->info("Sending notifications for campaign: {$campaign->title}");
        
        // Get all influencers with active WhatsApp numbers
        $influencers = User::whereHas('phones', function($query) {
            $query->where('status', 'ACTIVE');
        })->where('profile', 'INFLUENCER')->get();
        
        $this->info("Found {$influencers->count()} eligible influencers");
        
        $successCount = 0;
        $failedCount = 0;
        
        foreach ($influencers as $influencer) {
            $result = $this->whatsAppService->sendCampaignNotification($campaign, $influencer);
            
            if ($result['success']) {
                $successCount++;
                $this->info("✅ Notification sent to {$influencer->firstname} {$influencer->lastname}");
            } else {
                $failedCount++;
                $this->error("❌ Failed to send notification to {$influencer->firstname} {$influencer->lastname}");
            }
        }
        
        $this->info("Notification process completed: {$successCount} successful, {$failedCount} failed");
        
        return Command::SUCCESS;
    }
}