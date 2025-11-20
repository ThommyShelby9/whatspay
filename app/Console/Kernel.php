<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Les commandes Artisan fournies par votre application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\AssignDailyTasks::class,
    ];

    /**
     * Définir le planning des commandes de l'application.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Exécuter l'attribution des tâches à 12h00
        $schedule->command('tasks:assign-daily')
            /* ->dailyAt('12:00') */
            ->timezone('Africa/Porto-Novo'); // Timezone GMT+1 pour le Bénin

        // Process daily payments for influencers at 2 AM every day
        $schedule->command('payments:process-daily')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/daily-payments.log'));

        // Cleanup expired payment transactions every hour
        $schedule->command('payments:cleanup-expired')
            ->hourly();

        // Send payment reminders to clients with low balance (weekly)
        $schedule->command('payments:send-balance-reminders')
            ->weeklyOn(1, '09:00'); // Every Monday at 9 AM

        // Run every 30 minutes to catch assignments at the ~23.5 hour mark
        $schedule->command('whatspay:send-screenshot-reminders')
            ->everyThirtyMinutes();
    }


    /**
     * Enregistrer les commandes pour l'application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
