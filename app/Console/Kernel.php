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
                 ->dailyAt('12:00')
                 ->timezone('Africa/Porto-Novo'); // Timezone GMT+1 pour le Bénin
    }

    /**
     * Enregistrer les commandes pour l'application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}