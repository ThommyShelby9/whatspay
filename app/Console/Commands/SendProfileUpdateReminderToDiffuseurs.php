<?php

namespace App\Console\Commands;

use App\Mail\UpdateProfileReminder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendProfileUpdateReminderToDiffuseurs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatspay:send-profile-update-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie un rappel de mise à jour de profil à tous les diffuseurs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Trouver d'abord l'ID du rôle DIFFUSEUR
        $diffuseurRole = Role::where('typerole', 'DIFFUSEUR')->first();
        
        if (!$diffuseurRole) {
            $this->error("Rôle DIFFUSEUR introuvable!");
            return 1;
        }
        
        // Récupérer tous les utilisateurs ayant le rôle DIFFUSEUR
        $diffuseurs = User::whereHas('roles', function($query) use ($diffuseurRole) {
            $query->where('roles.id', $diffuseurRole->id);
        })->get();
        
        $count = $diffuseurs->count();
        $this->info("Envoi d'emails à {$count} diffuseurs...");
        
        if ($count === 0) {
            $this->warn("Aucun diffuseur trouvé.");
            return 0;
        }
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($diffuseurs as $diffuseur) {
            try {
                Mail::to($diffuseur->email)->send(new UpdateProfileReminder($diffuseur));
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'envoi à {$diffuseur->email}: {$e->getMessage()}");
                $failCount++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info("Emails envoyés avec succès: {$successCount}");
        
        if ($failCount > 0) {
            $this->warn("Emails échoués: {$failCount}");
        }
        
        return 0;
    }
}