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
        $query = Assignment::select([
                'assignments.*',
                'tasks.name as task_name',
                'users.firstname as agent_firstname',
                'users.lastname as agent_lastname'
            ])
            ->leftJoin('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->leftJoin('users', 'assignments.agent_id', '=', 'users.id');
        
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
    
    public function getAgentAssignments($agentId)
    {
        return Assignment::select([
                'assignments.*',
                'tasks.name as task_name'
            ])
            ->leftJoin('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->where('assignments.agent_id', $agentId)
            ->orderBy('assignments.created_at', 'desc')
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
                'status' => 'PENDING',
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
                $gain = $data['vues'] * 0.01; // Exemple: 1 centime par vue
            }
            
            Assignment::where('id', $id)->update([
                'vues' => $data['vues'],
                'files' => $data['files'],
                'status' => 'COMPLETED',
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
    
    public function getAvailableAgentsForTask($taskId, $filters = [])
    {
        $maxAssignments = !empty($filters['max_assignments']) ? $filters['max_assignments'] : 3;
        
        $query = User::select([
                'users.*',
                DB::raw('COUNT(assignments.id) as active_assignments')
            ])
            ->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
            ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
            ->leftJoin('assignments', function($join) {
                $join->on('users.id', '=', 'assignments.agent_id')
                     ->where('assignments.status', '!=', 'COMPLETED');
            })
            ->where('roles.typerole', 'DIFFUSEUR')
            ->where('users.enabled', true)
            ->groupBy('users.id');
        
        if (!empty($filters['category_id'])) {
            $query->whereIn('users.id', function($subquery) use ($filters) {
                $subquery->select('user_id')
                    ->from('category_user')
                    ->where('category_id', $filters['category_id']);
            });
        }
        
        return $query->havingRaw('COUNT(assignments.id) < ?', [$maxAssignments])
            ->orderBy('active_assignments', 'asc')
            ->get();
    }
    
    public function getAssignmentStats()
    {
        return [
            'total' => Assignment::count(),
            'pending' => Assignment::where('status', 'PENDING')->count(),
            'completed' => Assignment::where('status', 'COMPLETED')->count(),
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
                ->where('status', 'COMPLETED')
                ->count(),
            'total_vues' => Assignment::where('agent_id', $agentId)
                ->sum('vues'),
            'total_gain' => Assignment::where('agent_id', $agentId)
                ->sum('gain'),
        ];
    }
    
    public function getRecentAgentAssignments($agentId, $limit = 5)
    {
        return Assignment::select([
                'assignments.*',
                'tasks.name as task_name'
            ])
            ->join('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->where('assignments.agent_id', $agentId)
            ->orderBy('assignments.created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public function getAgentEarningsStats($agentId)
    {
        // Récupérer le total des gains
        $totalGain = Assignment::where('agent_id', $agentId)->sum('gain');
        
        // Récupérer les gains du mois en cours
        $currentMonth = Carbon::now();
        $thisMonthGain = Assignment::where('agent_id', $agentId)
            ->whereYear('submission_date', $currentMonth->year)
            ->whereMonth('submission_date', $currentMonth->month)
            ->sum('gain');
        
        // Récupérer les gains du mois précédent
        $lastMonth = Carbon::now()->subMonth();
        $lastMonthGain = Assignment::where('agent_id', $agentId)
            ->whereYear('submission_date', $lastMonth->year)
            ->whereMonth('submission_date', $lastMonth->month)
            ->sum('gain');
        
        // Calculer la moyenne mensuelle
        $monthlyAverage = DB::table(DB::raw('(
                SELECT 
                    EXTRACT(YEAR FROM submission_date) as year,
                    EXTRACT(MONTH FROM submission_date) as month,
                    SUM(gain) as monthly_gain
                FROM assignments
                WHERE agent_id = :agent_id AND submission_date IS NOT NULL
                GROUP BY year, month
            ) as monthly_gains'))
            ->selectRaw('AVG(monthly_gain) as average')
            ->setBindings(['agent_id' => $agentId])
            ->first();
        
        return [
            'total_gain' => $totalGain,
            'this_month' => $thisMonthGain,
            'last_month' => $lastMonthGain,
            'monthly_average' => $monthlyAverage ? $monthlyAverage->average : 0,
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
            // Retourner des statistiques par défaut si la colonne n'existe pas
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
        
        // Récupération de la base de requête
        $baseQuery = Assignment::join('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->where('tasks.client_id', $clientId);
        
        // Récupération du nombre total d'affectations
        $totalAssignments = (clone $baseQuery)->count();
        
        // Récupération du nombre d'affectations par statut
        $pendingAssignments = (clone $baseQuery)
            ->where('assignments.status', Util::ASSIGNMENTS_STATUSES["PENDING"])
            ->count();
        
        $acceptedAssignments = (clone $baseQuery)
            ->where('assignments.status', Util::ASSIGNMENTS_STATUSES["ACCEPTED"])
            ->count();
        
        $submittedAssignments = (clone $baseQuery)
            ->whereIn('assignments.status', [
                Util::ASSIGNMENTS_STATUSES["SUBMITED"],
                Util::ASSIGNMENTS_STATUSES["SUBMISSION_ACCEPTED"],
                Util::ASSIGNMENTS_STATUSES["SUBMISSION_REJECTED"]
            ])
            ->count();
        
        $paidAssignments = (clone $baseQuery)
            ->where('assignments.status', Util::ASSIGNMENTS_STATUSES["PAID"])
            ->count();
        
        $rejectedAssignments = (clone $baseQuery)
            ->where('assignments.status', Util::ASSIGNMENTS_STATUSES["REJECTED"])
            ->count();
        
        // Récupération du budget utilisé (payé et en attente)
        $paidBudget = (clone $baseQuery)
            ->where('assignments.status', Util::ASSIGNMENTS_STATUSES["PAID"])
            ->sum('assignments.gain');
        
        $pendingBudget = (clone $baseQuery)
            ->whereIn('assignments.status', [
                Util::ASSIGNMENTS_STATUSES["PENDING"], 
                Util::ASSIGNMENTS_STATUSES["ACCEPTED"],
                Util::ASSIGNMENTS_STATUSES["SUBMITED"],
                Util::ASSIGNMENTS_STATUSES["SUBMISSION_ACCEPTED"],
                Util::ASSIGNMENTS_STATUSES["SUBMISSION_REJECTED"]
            ])
            ->sum('assignments.gain');
        
        return [
            'total' => $totalAssignments ?? 0,
            'pending' => $pendingAssignments ?? 0,
            'accepted' => $acceptedAssignments ?? 0,
            'submitted' => $submittedAssignments ?? 0,
            'paid' => $paidAssignments ?? 0,
            'rejected' => $rejectedAssignments ?? 0,
            'paid_budget' => $paidBudget ?? 0,
            'pending_budget' => $pendingBudget ?? 0
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
            return [];
        }
        
        // Vérifier si la colonne task_id existe réellement
        if (!Schema::hasColumn('assignments', 'task_id')) {
            return [];
        }
        
        $taskIds = is_array($taskIds) ? $taskIds : [$taskIds];
        
        return Assignment::select([
                'assignments.*',
                DB::raw("CONCAT(users.firstname, ' ', users.lastname) as agent_name"),
                'users.email as agent_email'
            ])
            ->leftJoin('users', 'assignments.agent_id', '=', 'users.id')
            ->whereIn('assignments.task_id', $taskIds)
            ->orderBy('assignments.created_at', 'desc')
            ->get();
    }
}