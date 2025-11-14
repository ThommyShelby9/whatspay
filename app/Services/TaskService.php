<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Task;
use App\Models\Category;
use App\Models\Locality;
use App\Models\Occupation;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TaskService
{
    use Utils;

    protected $trackingService;

    public function __construct(
        TrackingService $trackingService,
    ) {
        $this->trackingService = $trackingService;
    }

    /**
     * Récupère toutes les tâches
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTasks()
    {
        return Task::with(['client'])->orderBy('created_at', 'desc')->get();
    }

    /**
     * Récupère les tâches d'un client
     * 
     * @param string $clientId ID du client
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClientTasks($clientId)
    {
        return Task::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Récupère les tâches d'un agent
     * 
     * @param string $agentId ID de l'agent
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAgentTasks($agentId)
    {
        return DB::table('assignments')
            ->join('tasks', 'assignments.task_id', '=', 'tasks.id')
            ->where('assignments.agent_id', $agentId)
            ->select(
                'tasks.*',
                'assignments.id as assignment_id',
                'assignments.status as assignment_status'
            )
            ->orderBy('tasks.created_at', 'desc')
            ->get();
    }

    /**
     * Récupère une tâche par son ID
     * 
     * @param string $id ID de la tâche
     * @return Task|null
     */
    public function getTaskById($id)
    {
        return Task::find($id);
    }

    /**
     * Récupère une tâche avec toutes ses relations
     * 
     * @param string $id ID de la tâche
     * @return Task|null
     */
    public function getTaskWithRelations($id)
    {
        return Task::with(['categories', 'localities', 'occupations', 'client'])
            ->find($id);
    }

    /**
     * Récupère les tâches selon des filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTasks($filters = [])
    {
        $query = Task::query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->whereHas('categories', function ($q) use ($filters) {
                $q->where('categories.id', $filters['category_id']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->where('startdate', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('enddate', '<=', $filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Crée une nouvelle tâche
     * 
     * @param array $taskData Données de la tâche
     * @return array Résultat de l'opération
     */
    public function createTask($taskData)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la création de la tâche',
            'task_id' => null
        ];

        try {
            DB::beginTransaction();

            $taskId = $this->getId();

            // Préparer les données de base de la tâche
            $task = [
                'id' => $taskId,
                'name' => $taskData['name'],
                'descriptipon' => $taskData['descriptipon'] ?? '',
                'files' => $taskData['files'] ?? null,
                'status' => Util::TASKS_STATUSES["PENDING"],
                'client_id' => $taskData['client_id'],
                'startdate' => $taskData['startdate'],
                'enddate' => $taskData['enddate'],
                'budget' => $taskData['budget'],
                'media_type' => $taskData['media_type'] ?? null,
                'legend' => $taskData['legend'] ?? null,
                'locality_id' => $taskData['locality_id'] ?? null,
                'occupation_id' => $taskData['occupation_id'] ?? null,
            ];

            // Créer la tâche
            $taskModel = Task::create($task);

            // Ajouter les champs optionnels s'ils existent dans le schéma
            if (!empty($taskData['url'])) {
                $tracking = $this->trackingService->generateTrackingLink($taskData['url'], $taskId);
                // Mettre à jour la tâche avec le lien de tracking
                $taskModel->update(['url' => $tracking['tracking_url']]);
            }

            // Associer les catégories
            if (!empty($taskData['categories'])) {
                $taskModel->categories()->attach($taskData['categories']);
            }

            // Associer les localités
            if (!empty($taskData['localities'])) {
                $taskModel->localities()->attach($taskData['localities']);
            }

            // Associer les occupations
            if (!empty($taskData['occupations'])) {
                $taskModel->occupations()->attach($taskData['occupations']);
            }

            DB::commit();

            $result['success'] = true;
            $result['message'] = 'Tâche créée avec succès';
            $result['task_id'] = $taskId;
        } catch (\Exception $e) {
            DB::rollBack();
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Met à jour une tâche existante
     * 
     * @param string $id ID de la tâche
     * @param array $taskData Nouvelles données
     * @return array Résultat de l'opération
     */
    public function updateTask($id, $taskData)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la mise à jour de la tâche'
        ];

        try {
            $task = Task::find($id);

            if (!$task) {
                $result['message'] = 'Tâche non trouvée';
                return $result;
            }

            DB::beginTransaction();

            // Mettre à jour les données de base
            $updateData = [
                'name' => $taskData['name'] ?? $task->name,
                'descriptipon' => $taskData['descriptipon'] ?? $task->descriptipon,
                'files' => $taskData['files'] ?? $task->files,
                'startdate' => $taskData['startdate'] ?? $task->startdate,
                'enddate' => $taskData['enddate'] ?? $task->enddate,
                'budget' => $taskData['budget'] ?? $task->budget,
            ];

            // Champs optionnels
            if (Schema::hasColumn('tasks', 'media_type')) {
                $updateData['media_type'] = $taskData['media_type'] ?? $task->media_type;
            }

            if (!empty($taskData['url'])) {
                // Générer un nouveau lien de tracking si l'URL a changé
                $tracking = $this->trackingService->generateTrackingLink($taskData['url'], $id);
                $updateData['url'] = $tracking['tracking_url'];
            } else if (Schema::hasColumn('tasks', 'url')) {
                $updateData['url'] = $taskData['url'] ?? $task->url;
            }

            if (Schema::hasColumn('tasks', 'legend')) {
                $updateData['legend'] = $taskData['legend'] ?? $task->legend;
            }

            $task->update($updateData);

            // Mettre à jour les relations
            if (isset($taskData['categories'])) {
                $task->categories()->sync($taskData['categories']);
            }

            if (isset($taskData['localities'])) {
                $task->localities()->sync($taskData['localities']);
            }

            if (isset($taskData['occupations'])) {
                $task->occupations()->sync($taskData['occupations']);
            }

            DB::commit();

            $result['success'] = true;
            $result['message'] = 'Tâche mise à jour avec succès';
        } catch (\Exception $e) {
            DB::rollBack();
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Supprime une tâche
     * 
     * @param string $id ID de la tâche
     * @return array Résultat de l'opération
     */
    public function deleteTask($id)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la suppression de la tâche'
        ];

        try {
            $task = Task::find($id);

            if (!$task) {
                $result['message'] = 'Tâche non trouvée';
                return $result;
            }

            DB::beginTransaction();

            // Détacher toutes les relations
            $task->categories()->detach();

            if (method_exists($task, 'localities')) {
                $task->localities()->detach();
            }

            if (method_exists($task, 'occupations')) {
                $task->occupations()->detach();
            }

            // Supprimer la tâche (soft delete si le modèle utilise SoftDeletes)
            $task->delete();

            DB::commit();

            $result['success'] = true;
            $result['message'] = 'Tâche supprimée avec succès';
        } catch (\Exception $e) {
            DB::rollBack();
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Approuve une tâche
     * 
     * @param string $id ID de la tâche
     * @param string $validateurId ID du validateur
     * @return array Résultat de l'opération
     */
    public function approveTask($id, $validateurId)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'approbation de la tâche'
        ];

        try {
            $task = Task::find($id);

            if (!$task) {
                $result['message'] = 'Tâche non trouvée';
                return $result;
            }

            $task->update([
                'status' => Util::TASKS_STATUSES["ACCEPTED"],
                'validation_date' => now(),
                'validateur_id' => $validateurId
            ]);

            $result['success'] = true;
            $result['message'] = 'Tâche approuvée avec succès';
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Rejette une tâche
     * 
     * @param string $id ID de la tâche
     * @param string $validateurId ID du validateur
     * @param string $reason Motif de rejet
     * @return array Résultat de l'opération
     */
    public function rejectTask($id, $validateurId, $reason)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors du rejet de la tâche'
        ];

        try {
            $task = Task::find($id);

            if (!$task) {
                $result['message'] = 'Tâche non trouvée';
                return $result;
            }

            $task->update([
                'status' => Util::TASKS_STATUSES["REJECTED"],
                'validation_date' => now(),
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

    /**
     * Récupère les statistiques globales des tâches
     * 
     * @return array Statistiques
     */
    public function getTaskStats()
    {
        return [
            'total' => Task::count(),
            'pending' => Task::where('status', Util::TASKS_STATUSES["PENDING"])->count(),
            'accepted' => Task::where('status', Util::TASKS_STATUSES["ACCEPTED"])->count(),
            'rejected' => Task::where('status', Util::TASKS_STATUSES["REJECTED"])->count(),
            'paid' => Task::where('status', Util::TASKS_STATUSES["PAID"])->count(),
            'closed' => Task::where('status', Util::TASKS_STATUSES["CLOSED"])->count(),
            'total_budget' => Task::sum('budget') ?? 0,
        ];
    }

    /**
     * Récupère les statistiques des tâches d'un client
     * 
     * @param string $clientId ID du client
     * @return array Statistiques
     */
    public function getClientTaskStats($clientId)
    {
        return [
            'total' => Task::where('client_id', $clientId)->count(),
            'pending' => Task::where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["PENDING"])->count(),
            'accepted' => Task::where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["ACCEPTED"])->count(),
            'paid' => Task::where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["PAID"])->count(),
            'rejected' => Task::where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["REJECTED"])->count(),
            'closed' => Task::where('client_id', $clientId)->where('status', Util::TASKS_STATUSES["CLOSED"])->count(),
            'total_budget' => Task::where('client_id', $clientId)->sum('budget') ?? 0,
        ];
    }

    /**
     * Récupère les tâches récentes
     * 
     * @param int $limit Nombre de tâches à récupérer
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentTasks($limit = 5)
    {
        return Task::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Récupère les tâches récentes d'un client
     * 
     * @param string $clientId ID du client
     * @param int $limit Nombre de tâches à récupérer
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentClientTasks($clientId, $limit = 5)
    {
        return Task::where('client_id', $clientId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
