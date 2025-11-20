<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\Utils;

class TaskAssignmentService
{
    use Utils;

    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Attribution automatique des tâches aux diffuseurs éligibles
     * À exécuter chaque jour à minuit
     */
    public function assignDailyTasks()
    {
        Log::info('Début de l\'attribution automatique des tâches');

        // Récupérer les tâches actives avec du budget
        $activeTasks = Task::where('status', 'APPROVED')
            ->where('startdate', '<=', now())
            ->where('enddate', '>=', now())
            ->where('budget', '>', 0)
            ->get();

        $totalEligible = 0;

        foreach ($activeTasks as $task) {
            $eligibleForTask =  $this->assignTaskToEligibleAgents($task);

            //$totalEligible += $eligibleForTask;
        }
        return $activeTasks->count();

        Log::info('Fin de l\'attribution automatique des tâches');
    }

    /**
     * Attribue une tâche aux agents éligibles selon les critères
     */
    private function assignTaskToEligibleAgents(Task $task)
    {
        // Construire la requête pour les agents éligibles
        $query = User::whereHas('roles', function ($q) {
            $q->where('typerole', 'DIFFUSEUR');
        })->where('enabled', true);

        // Filtrer par localité si spécifiée
        if (!empty($task->locality_id)) {
            $query->where('locality_id', $task->locality_id);
        }

        // Filtrer par profession si spécifiée
        if (!empty($task->occupation_id)) {
            $query->where('occupation_id', $task->occupation_id);
        }

        // Limiter aux agents qui n'ont pas plus de 3 tâches actives
        $eligibleAgents = $query->whereDoesntHave('assignmentsAsAgent', function ($q) {
            $q->whereIn('status', ['PENDING', 'ACCEPTED']);
        }, '>=', 3)->get();

        // Calculer le budget quotidien disponible
        $remainingDays = Carbon::now()->diffInDays(Carbon::parse($task->enddate)) + 1;
        $dailyBudget = $remainingDays > 0 ? ($task->budget / $remainingDays) : $task->budget;

        // Calculer le total des vues attendues
        $totalEstimatedViews = $eligibleAgents->sum('vuesmoyen');

        foreach ($eligibleAgents as $agent) {
            // Vérifier si l'agent n'est pas déjà assigné à cette tâche aujourd'hui
            $alreadyAssigned = Assignment::where('agent_id', $agent->id)
                ->where('task_id', $task->id)
                ->whereDate('assignment_date', Carbon::today())
                ->exists();

            if ($alreadyAssigned) {
                continue;
            }

            // Calculer le gain prévu basé sur le nombre moyen de vues
            // 1 FR par vue selon le taux fixe spécifié
            $expectedViews = $agent->vuesmoyen;
            $expectedGain = $expectedViews;

            // Attribuer la tâche
            $assignmentId = $this->getId();
            Assignment::create([
                'id' => $assignmentId,
                'task_id' => $task->id,
                'agent_id' => $agent->id,
                'assigner_id' => null, // Système automatique
                'assignment_date' => Carbon::now(),
                'status' => 'PENDING',
                'vues' => 0,
                'expected_views' => $expectedViews,
                'gain' => 0, // Gain réel à calculer après soumission
                'expected_gain' => $expectedGain
            ]);

            Log::info("Tâche {$task->id} assignée à l'agent {$agent->id}, vues attendues: {$expectedViews}, gain potentiel: {$expectedGain}F");
        }

        return 100;
    }

    /**
     * Vérifie si l'heure actuelle est dans la fenêtre de soumission (11h-12h)
     */
    public function isInSubmissionWindow()
    {
        $now = Carbon::now('GMT+1');
        $hour = $now->hour;

        return $hour >= 11 && $hour < 12;
    }

    /**
     * Traite la soumission d'un résultat de campagne
     */
    public function submitResult($id, $data)
    {
        // Vérifier si nous sommes dans la fenêtre de soumission
        if (!$this->isInSubmissionWindow()) {
            return [
                'success' => false,
                'message' => 'Les soumissions ne sont acceptées qu\'entre 11h et 12h.'
            ];
        }

        try {
            $assignment = Assignment::find($id);

            if (!$assignment) {
                return [
                    'success' => false,
                    'message' => 'Affectation non trouvée'
                ];
            }

            if ($assignment->agent_id != $data['agent_id']) {
                return [
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à soumettre un résultat pour cette affectation'
                ];
            }

            // Calculer le gain basé sur les vues (1F par vue)
            $gain = $data['vues'];

            Assignment::where('id', $id)->update([
                'vues' => $data['vues'],
                'files' => $data['files'],
                'status' => 'COMPLETED',
                'submission_date' => Carbon::now(),
                'gain' => $gain
            ]);

            // Mettre à jour le budget de la campagne
            $task = Task::find($assignment->task_id);
            if ($task) {
                $task->budget -= $gain;
                $task->save();
            }

            return [
                'success' => true,
                'message' => 'Résultat soumis avec succès'
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la soumission du résultat: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }
}
