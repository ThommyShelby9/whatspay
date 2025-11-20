<?php

namespace App\Console\Commands;

use App\Services\TaskAssignmentService;
use Illuminate\Console\Command;

class AssignDailyTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:assign-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attribue automatiquement les tâches aux diffuseurs éligibles';

    /**
     * Execute the console command.
     *
     * @param TaskAssignmentService $assignmentService
     * @return int
     */
    public function handle(TaskAssignmentService $assignmentService)
    {
        $this->info('Début de l\'attribution des tâches...');

        $count = $assignmentService->assignDailyTasks();

        $this->info("Nombre de diffuseurs éligibles : {$count}");

        \Log::channel('daily')->info("Assignation quotidienne – {$count} diffuseurs éligibles.");

        $this->info('Attribution des tâches terminée avec succès.');

        return 0;
    }
}
