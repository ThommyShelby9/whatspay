<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Assignment;
use App\Models\Task;
use App\Models\User;
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssignmentService
{
    use Utils;

    public function getAssignments($filters = [])
    {
        $query = Assignment::with([
            'task',
            'agent'
        ]);

        if (!empty($filters['status'])) {
            $query->where('assignments.status', $filters['status']);
        }

        if (!empty($filters['task_id'])) {
            $query->where('assignments.task_id', $filters['task_id']);
        }

        if (!empty($filters['agent_id'])) {
            $query->where('assignments.agent_id', $filters['agent_id']);
        }

        return $query->orderBy('assignments.created_at', 'desc')->get();
    }

    public function getAllAssignments()
    {
        return $this->getAssignments();
    }

    public function getClientAssignments($clientId)
    {
        return Assignment::select([
            'assignments.*',
            'tasks.name as task_name',
            'users.firstname as agent_firstname',
            'users.lastname as agent_lastname'
        ])
            ->leftJoin('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->leftJoin('users', 'assignments.agent_id', '=', 'users.id')
            ->where('tasks.client_id', $clientId)
            ->orderBy('assignments.created_at', 'desc')
            ->get();
    }

    public function getAvailableAgentTasks($agentId)
    {
        return Assignment::where('agent_id', $agentId)
            ->where('status', 'ASSIGNED')
            ->with('task')
            ->get();
    }

    public function getAgentAssignments($agentId)
    {
        return Assignment::with('task')   // charge la relation Task
            ->where('agent_id', $agentId)
            ->where('status', '!=', 'ASSIGNED')
            ->orderByDesc('created_at')
            ->get();
    }

    public function getAssignmentById($id)
    {
        $assignment = Assignment::select([
            'assignments.*',
            'tasks.name as task_name',
            'tasks.descriptipon as task_description',
            'tasks.files as task_files',
            'tasks.startdate as task_startdate',
            'tasks.enddate as task_enddate',
            'tasks.budget as task_budget',
            'users.firstname as agent_firstname',
            'users.lastname as agent_lastname'
        ])
            ->leftJoin('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->leftJoin('users', 'assignments.agent_id', '=', 'users.id')
            ->where('assignments.id', $id)
            ->first();

        return $assignment;
    }

    public function createAssignment($assignmentData)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la création de l\'affectation',
            'assignment_id' => null
        ];

        try {
            // Vérifier que l'agent n'a pas déjà trop de tâches
            $activeAssignments = Assignment::where('agent_id', $assignmentData['agent_id'])
                ->where('status', '!=', 'COMPLETED')
                ->count();

            if ($activeAssignments >= 3) {
                $result['message'] = 'Cet agent a déjà atteint sa limite de tâches actives';
                return $result;
            }

            $assignmentId = $this->getId();
            $assignment = [
                'id' => $assignmentId,
                'task_id' => $assignmentData['task_id'],
                'assignment_date' => Carbon::now()->toDateTimeString(),
                'assigner_id' => $assignmentData['assigner_id'],
                'response_date' => null,
                'agent_id' => $assignmentData['agent_id'],
                'status' => 'ASSIGNED',
                'submission_date' => null,
                'vues' => 0,
                'files' => null,
                'gain' => 0
            ];

            Assignment::create($assignment);

            $result['success'] = true;
            $result['message'] = 'Affectation créée avec succès';
            $result['assignment_id'] = $assignmentId;
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    public function updateAssignment($id, $assignmentData)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la mise à jour de l\'affectation'
        ];

        try {
            $assignment = Assignment::find($id);

            if (!$assignment) {
                $result['message'] = 'Affectation non trouvée';
                return $result;
            }

            $updateData = [];

            if (isset($assignmentData['status'])) {
                $updateData['status'] = $assignmentData['status'];
            }

            if (isset($assignmentData['vues'])) {
                $updateData['vues'] = $assignmentData['vues'];
            }

            if (isset($assignmentData['files'])) {
                $updateData['files'] = $assignmentData['files'];
            }

            if (isset($assignmentData['gain'])) {
                $updateData['gain'] = $assignmentData['gain'];
            }

            if (count($updateData) > 0) {
                Assignment::where('id', $id)->update($updateData);
            }

            $result['success'] = true;
            $result['message'] = 'Affectation mise à jour avec succès';
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    public function deleteAssignment($id)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression de l\'affectation'
        ];

        try {
            $assignment = Assignment::find($id);

            if (!$assignment) {
                $result['message'] = 'Affectation non trouvée';
                return $result;
            }

            Assignment::where('id', $id)->delete();

            $result['success'] = true;
            $result['message'] = 'Affectation supprimée avec succès';
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    public function submitResult($id, $data)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la soumission du résultat'
        ];

        try {
            $assignment = Assignment::find($id);

            if (!$assignment) {
                $result['message'] = 'Affectation non trouvée';
                return $result;
            }

            if ($assignment->agent_id != $data['agent_id']) {
                $result['message'] = 'Vous n\'êtes pas autorisé à soumettre un résultat pour cette affectation';
                return $result;
            }

            // Calculer le gain basé sur les vues
            $task = Task::find($assignment->task_id);
            $gain = 0;

            if ($task) {
                // Logique de calcul du gain (à adapter selon votre logique métier)
                $gain = $data['vues']; // Exemple: 1 centime par vue
            }

            Assignment::where('id', $id)->update([
                'vues' => $data['vues'],
                'files' => $data['files'],
                'status' => Util::ASSIGNMENTS_STATUSES['SUBMITED'],
                'submission_date' => Carbon::now()->toDateTimeString(),
                'gain' => $gain
            ]);

            $result['success'] = true;
            $result['message'] = 'Résultat soumis avec succès';
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    public function validate($assignment, $request)
    {
        if ($request->has('vues')) {

            $request->validate([
                'vues' => 'required|integer|min:0',
            ]);

            $assignment->vues = $request->vues;
            $assignment->gain = $request->vues;
            $assignment->save();

            return [
                "success" => true,
                "message" => "Nombre de vues mis à jour avec succès."
            ];
        }

        $agent = $assignment->agent;

        if (!$agent) {
            return [
                "success" => false,
                "message" => "Impossible de valider : agent introuvable."
            ];
        }

        // Mettre le statut SUBMISSION_VALIDATE
        $assignment->status = Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'];
        $assignment->save();

        // Calcul du gain à créditer
        $gain = $assignment->gain ?? 0;

        // Créditer l’agent
        /* $agent->wallet->credit($gain);
        $agent->save(); */

        return [
            "success" => true,
            "message" => "Résultat validé et gain crédité à l'agent."
        ];
    }

    public function getAvailableAgentsForTask($taskId, $filters = [])
    {
        $maxAssignments = $filters['max_assignments'] ?? 6;

        $query = User::whereHas('roles', function ($q) {
            $q->where('typerole', 'DIFFUSEUR');
        })
            ->where('enabled', true)
            ->withCount(['assignments as active_assignments' => function ($q) {
                $q->where('status', '!=', 'SUBMITED');
            }]);

        // Filtre par catégorie si défini
        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('category_id', $filters['category_id']);
            });
        }

        return $query->having('active_assignments', '<', $maxAssignments)
            ->orderBy('active_assignments', 'asc')
            ->get();
    }


    public function getAssignmentStats()
    {
        return [
            'total' => Assignment::count(),
            'pending' => Assignment::where('status', 'PENDING')->count(),
            'completed' => Assignment::where('status', 'SUBMITED')->orWhere('status', 'SUBMISSION_ACCEPTED')->count(),
            'total_vues' => Assignment::sum('vues'),
            'total_gain' => Assignment::sum('gain'),
        ];
    }

    public function getAgentAssignmentStats($agentId)
    {
        return [
            'total' => Assignment::where('agent_id', $agentId)->count(),
            'pending' => Assignment::where('agent_id', $agentId)
                ->where('status', 'PENDING')
                ->count(),
            'completed' => Assignment::where('agent_id', $agentId)
                ->where('status', 'SUBMITED')
                ->orWhere('status', 'SUBMISSION_ACCEPTED')
                ->count(),
            'total_vues' => Assignment::where('agent_id', $agentId)
                ->sum('vues'),
            'total_gain' => Assignment::where('agent_id', $agentId)
                ->sum('gain'),
        ];
    }

    public function getRecentAgentAssignments($agentId, $limit = 5)
    {
        return Assignment::with('task')      // eager load la relation task
            ->where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($assignment) {
                // ajouter task_name pour compatibilité avec l'ancien code
                $assignment->task_name = $assignment->task->name ?? null;
                return $assignment;
            });
    }

    public function getAgentEarningsStats($agentId)
    {
        $totalGain = Assignment::where('agent_id', $agentId)->sum('gain');

        $currentMonth = Carbon::now();

        $thisMonthGain = Assignment::where('agent_id', $agentId)
            ->whereYear('submission_date', $currentMonth->year)
            ->whereMonth('submission_date', $currentMonth->month)
            ->sum('gain');

        $lastMonth = Carbon::now()->subMonth();

        $lastMonthGain = Assignment::where('agent_id', $agentId)
            ->whereYear('submission_date', $lastMonth->year)
            ->whereMonth('submission_date', $lastMonth->month)
            ->sum('gain');

        $monthlyGains = Assignment::where('agent_id', $agentId)
            ->whereNotNull('submission_date')
            ->select(
                DB::raw('EXTRACT(YEAR FROM submission_date) AS year'),
                DB::raw('EXTRACT(MONTH FROM submission_date) AS month'),
                DB::raw('SUM(gain) AS monthly_gain')
            )
            ->groupBy('year', 'month')
            ->get();

        $monthlyAverage = $monthlyGains->avg('monthly_gain');

        return [
            'total_gain' => $totalGain,
            'this_month' => $thisMonthGain,
            'last_month' => $lastMonthGain,
            'monthly_average' => $monthlyAverage ?? 0,
        ];
    }

    /**
     * Récupère les statistiques d'affectation pour un client spécifique
     * 
     * @param string $clientId ID du client
     * @return array Statistiques d'affectation
     */
    public function getClientAssignmentStats($clientId)
    {
        // Vérifier si la colonne task_id existe réellement
        if (!Schema::hasColumn('assignments', 'task_id')) {
            return [
                'total' => 0,
                'pending' => 0,
                'accepted' => 0,
                'submitted' => 0,
                'paid' => 0,
                'rejected' => 0,
                'paid_budget' => 0,
                'pending_budget' => 0
            ];
        }

        // Récupérer toutes les affectations du client via les relations Eloquent
        $assignments = Assignment::whereHas('task', function ($query) use ($clientId) {
            $query->where('client_id', $clientId);
        });

        $total = $assignments->count();

        $pending = (clone $assignments)
            ->where('status', Util::ASSIGNMENTS_STATUSES['PENDING'])
            ->count();

        $accepted = (clone $assignments)
            ->where('status', Util::ASSIGNMENTS_STATUSES['ASSIGNED'])
            ->count();

        $submitted = (clone $assignments)
            ->whereIn('status', [
                Util::ASSIGNMENTS_STATUSES['SUBMITED'],
                Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'],
                Util::ASSIGNMENTS_STATUSES['SUBMISSION_REJECTED']
            ])
            ->count();

        $paid = (clone $assignments)
            ->where('status', Util::ASSIGNMENTS_STATUSES['PAID'])
            ->count();

        $rejected = (clone $assignments)
            ->where('status', Util::ASSIGNMENTS_STATUSES['REJECTED'])
            ->count();

        $paidBudget = (clone $assignments)
            ->where('status', Util::ASSIGNMENTS_STATUSES['PAID'])
            ->sum('gain');

        $pendingBudget = (clone $assignments)
            ->whereIn('status', [
                Util::ASSIGNMENTS_STATUSES['PENDING'],
                Util::ASSIGNMENTS_STATUSES['ASSIGNED'],
                Util::ASSIGNMENTS_STATUSES['SUBMITED'],
                Util::ASSIGNMENTS_STATUSES['SUBMISSION_ACCEPTED'],
                Util::ASSIGNMENTS_STATUSES['SUBMISSION_REJECTED']
            ])
            ->sum('gain');

        return [
            'total' => $total,
            'pending' => $pending,
            'accepted' => $accepted,
            'submitted' => $submitted,
            'paid' => $paid,
            'rejected' => $rejected,
            'paid_budget' => $paidBudget,
            'pending_budget' => $pendingBudget
        ];
    }


    /**
     * Récupère les affectations pour un ensemble de tâches
     * 
     * @param array $taskIds Liste des IDs de tâches
     * @return array Liste des affectations
     */
    public function getAssignmentsByTasks($taskIds)
    {
        if (empty($taskIds)) {
            return collect(); // Retourne une collection vide
        }

        $taskIds = is_array($taskIds) ? $taskIds : [$taskIds];

        return Assignment::with('agent') // Charger les agents
            ->whereIn('task_id', $taskIds)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
