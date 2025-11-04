<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Task;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;

class TaskService
{
    use Utils;
    
    public function getAllTasks()
    {
        return DB::select("select * from tasks order by created_at desc");
    }
    
    public function getClientTasks($clientId)
    {
        return DB::select("select * from tasks where tasks.client_id = :client_id order by created_at desc", [
            'client_id' => $clientId
        ]);
    }
    
    public function getAgentTasks($agentId)
    {
        return DB::select("select
            tasks.*,
            assignments.id as assignment_id,
            assignments.status as assignment_status
         from assignments 
         left join tasks on assignments.task_id = tasks.id
         where assignments.agent_id = :agent_id order by created_at desc", [
            'agent_id' => $agentId
        ]);
    }
    
    public function getTaskById($id)
    {
        $tasks = DB::select("select * from tasks where id = :id", ['id' => $id]);
        return count($tasks) > 0 ? $tasks[0] : null;
    }
    
    public function getTasks($filters = [])
    {
        $query = "select * from tasks where 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $query .= " and status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['client_id'])) {
            $query .= " and client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (!empty($filters['category_id'])) {
            $query .= " and id in (select task_id from category_task where category_id = :category_id)";
            $params['category_id'] = $filters['category_id'];
        }
        
        $query .= " order by created_at desc";
        
        return DB::select($query, $params);
    }
 public function createTask($taskData)
{
    $result = [
        'success' => false,
        'message' => 'Une erreur est survenue lors de la création de la tâche',
        'task_id' => null
    ];
    
    try {
        $processingTransaction = false;
        DB::beginTransaction();
        $processingTransaction = true;
        
        $taskId = $this->getId();
        $task = [
            'id' => $taskId,
            'name' => $taskData['name'],
            'descriptipon' => $taskData['descriptipon'],
            'files' => $taskData['files'],
            'status' => Util::TASKS_STATUSES["PENDING"],
            'client_id' => $taskData['client_id'],
            'validation_date' => null,
            'validateur_id' => null,
            'startdate' => $taskData['startdate'],
            'enddate' => $taskData['enddate'],
            'budget' => $taskData['budget'],
            // Nouveaux champs
            'media_type' => $taskData['media_type'] ?? null,
            'url' => $taskData['url'] ?? null,
            'locality_id' => $taskData['locality_id'] ?? null,
            'occupation_id' => $taskData['occupation_id'] ?? null,
            'legend' => $taskData['legend'] ?? null
        ];
        
        Task::create($task);
        
        // Ajouter les catégories associées
        if (!empty($taskData['categories'])) {
            $date = gmdate('Y-m-d H:i:s');
            foreach ($taskData['categories'] as $categoryId) {
                DB::table('category_task')->insert([
                    'category_id' => $categoryId,
                    'task_id' => $taskId,
                    'updated_at' => $date,
                    'created_at' => $date,
                ]);
            }
        }
        
        DB::commit();
        $processingTransaction = false;
        
        $result['success'] = true;
        $result['message'] = 'Tâche créée avec succès';
        $result['task_id'] = $taskId;
        
    } catch (\Exception $e) {
        if ($processingTransaction) {
            DB::rollBack();
        }
        $result['message'] = 'Erreur: ' . $e->getMessage();
    }
    
    return $result;
}

public function updateTask($id, $taskData)
{
    $result = [
        'success' => false,
        'message' => 'Une erreur est survenue lors de la mise à jour de la tâche'
    ];
    
    try {
        $task = $this->getTaskById($id);
        if (!$task) {
            $result['message'] = 'Tâche non trouvée';
            return $result;
        }
        
        $processingTransaction = false;
        DB::beginTransaction();
        $processingTransaction = true;
        
        $updateData = [
            'name' => $taskData['name'],
            'descriptipon' => $taskData['descriptipon'],
            'files' => $taskData['files'],
            'startdate' => $taskData['startdate'],
            'enddate' => $taskData['enddate'],
            'budget' => $taskData['budget'],
            // Nouveaux champs
            'media_type' => $taskData['media_type'] ?? $task->media_type,
            'url' => $taskData['url'] ?? $task->url,
            'locality_id' => $taskData['locality_id'] ?? $task->locality_id,
            'occupation_id' => $taskData['occupation_id'] ?? $task->occupation_id,
            'legend' => $taskData['legend'] ?? $task->legend
        ];
        
        Task::where('id', $id)->update($updateData);
        
        // Mettre à jour les catégories si nécessaire
        if (!empty($taskData['categories'])) {
            // Supprimer les anciennes relations
            DB::table('category_task')->where('task_id', $id)->delete();
            
            // Ajouter les nouvelles relations
            $date = gmdate('Y-m-d H:i:s');
            foreach ($taskData['categories'] as $categoryId) {
                DB::table('category_task')->insert([
                    'category_id' => $categoryId,
                    'task_id' => $id,
                    'updated_at' => $date,
                    'created_at' => $date,
                ]);
            }
        }
        
        DB::commit();
        $processingTransaction = false;
        
        $result['success'] = true;
        $result['message'] = 'Tâche mise à jour avec succès';
        
    } catch (\Exception $e) {
        if ($processingTransaction) {
            DB::rollBack();
        }
        $result['message'] = 'Erreur: ' . $e->getMessage();
    }
    
    return $result;
}
    
    public function deleteTask($id)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression de la tâche'
        ];
        
        try {
            $task = $this->getTaskById($id);
            if (!$task) {
                $result['message'] = 'Tâche non trouvée';
                return $result;
            }
            
            $processingTransaction = false;
            DB::beginTransaction();
            $processingTransaction = true;
            
            // Supprimer les relations avec les catégories
            DB::table('category_task')->where('task_id', $id)->delete();
            
            // Supprimer la tâche (soft delete)
            Task::where('id', $id)->delete();
            
            DB::commit();
            $processingTransaction = false;
            
            $result['success'] = true;
            $result['message'] = 'Tâche supprimée avec succès';
            
        } catch (\Exception $e) {
            if ($processingTransaction) {
                DB::rollBack();
            }
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    public function approveTask($id, $validateurId)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'approbation de la tâche'
        ];
        
        try {
            $task = $this->getTaskById($id);
            if (!$task) {
                $result['message'] = 'Tâche non trouvée';
                return $result;
            }
            
            Task::where('id', $id)->update([
                'status' => Util::TASKS_STATUSES["APPROVED"],
                'validation_date' => gmdate('Y-m-d H:i:s'),
                'validateur_id' => $validateurId
            ]);
            
            $result['success'] = true;
            $result['message'] = 'Tâche approuvée avec succès';
            
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    public function rejectTask($id, $validateurId, $reason)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors du rejet de la tâche'
        ];
        
        try {
            $task = $this->getTaskById($id);
            if (!$task) {
                $result['message'] = 'Tâche non trouvée';
                return $result;
            }
            
            Task::where('id', $id)->update([
                'status' => Util::TASKS_STATUSES["REJECTED"],
                'validation_date' => gmdate('Y-m-d H:i:s'),
                'validateur_id' => $validateurId,
                'rejection_reason' => $reason
            ]);
            
            $result['success'] = true;
            $result['message'] = 'Tâche rejetée avec succès';
            
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    public function getTaskStats()
    {
        // Statistiques globales des tâches
$stats = [
    'total' => DB::table('tasks')->count(),
    'pending' => DB::table('tasks')->where('status', Util::TASKS_STATUSES["PENDING"])->count(),
    'accepted' => DB::table('tasks')->where('status', Util::TASKS_STATUSES["ACCEPTED"])->count(),
    'rejected' => DB::table('tasks')->where('status', Util::TASKS_STATUSES["REJECTED"])->count(),
    'paid' => DB::table('tasks')->where('status', Util::TASKS_STATUSES["PAID"])->count(),
    'closed' => DB::table('tasks')->where('status', Util::TASKS_STATUSES["CLOSED"])->count(),
    'total_budget' => DB::table('tasks')->sum('budget'),
];
        
        return $stats;
    }
    
    public function getClientTaskStats($clientId)
    {
        // Statistiques des tâches d'un client
$stats = [
    'total' => DB::table('tasks')->where('client_id', $clientId)->count(),
    'pending' => DB::table('tasks')->where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["PENDING"])->count(),
    'accepted' => DB::table('tasks')->where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["ACCEPTED"])->count(),
    'paid' => DB::table('tasks')->where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["PAID"])->count(),
    'rejected' => DB::table('tasks')->where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["REJECTED"])->count(),
    'closed' => DB::table('tasks')->where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["CLOSED"])->count(),
    'total_budget' => DB::table('tasks')->where('client_id', $clientId)->sum('budget'),
];
        
        return $stats;
    }
    
    public function getRecentTasks($limit = 5)
    {
        return DB::table('tasks')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public function getRecentClientTasks($clientId, $limit = 5)
    {
        return DB::table('tasks')
            ->where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}