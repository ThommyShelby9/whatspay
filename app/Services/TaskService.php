<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Assignment;
use App\Models\Task;
use App\Models\Category;
use App\Models\Locality;
use App\Models\Occupation;
use App\Models\User;
use App\Traits\Utils;
use Carbon\Carbon;
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

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['category_id'])) {
            // Nettoyer le tableau : enlever les null / vides
            $categoryIds = array_filter($filters['category_id'], fn($id) => !is_null($id) && $id !== '');
            if (!empty($categoryIds)) {
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
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

            // Récupérer un id 'principal' si un tableau est passé
            $primaryLocality = null;
            if (!empty($taskData['localities'])) {
                // si c'est un array, prends le premier élément
                $primaryLocality = is_array($taskData['localities']) ? ($taskData['localities'][0] ?? null) : $taskData['localities'];
            }

            $primaryOccupation = null;
            if (!empty($taskData['occupations'])) {
                $primaryOccupation = is_array($taskData['occupations']) ? ($taskData['occupations'][0] ?? null) : $taskData['occupations'];
            }

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
                'locality_id' => $primaryLocality,
                'occupation_id' => $primaryOccupation,
                'view_price' => $taskData['view_price'],
                'total_views_estimated' => (int) floor($taskData['budget'] / $taskData['view_price'])
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
        $result = ['success' => false, 'message' => 'Erreur lors de la mise à jour', 'task_id' => $id];

        try {
            DB::beginTransaction();

            $task = Task::find($id);
            if (!$task) return $result;

            // Fichiers
            $files = json_decode($task->files ?? '[]', true);
            if (!is_array($files)) $files = [];

            if (!empty($taskData['campaign_files'])) {
                $files = [];
                foreach ($taskData['campaign_files'] as $file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('uploads', $fileName, 'public');
                    $files[] = [
                        'name' => $fileName,
                        'original_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'path' => $filePath,
                        'url' => asset('storage/' . $filePath),
                        'size' => $file->getSize(),
                    ];
                }
            }

            // Données à mettre à jour
            $updateData = [
                'name' => $taskData['name'] ?? $task->name,
                'descriptipon' => $taskData['descriptipon'] ?? $task->descriptipon,
                'files' => !empty($files) ? json_encode($files) : $task->files,
                'startdate' => $taskData['startdate'] ?? $task->startdate,
                'enddate' => $taskData['enddate'] ?? $task->enddate,
                'budget' => $taskData['budget'] ?? $task->budget,
                'media_type' => $taskData['media_type'] ?? $task->media_type,
                'legend' => $taskData['legend'] ?? $task->legend,
                'view_price' => $taskData['view_price'] ?? $task->view_price,
                'total_views_estimated' =>  (int) floor($taskData['budget'] / $taskData['view_price']) ?? $task->total_views_estimated
            ];

            // URL tracking
            if (!empty($taskData['url'])) {
                $tracking = $this->trackingService->generateTrackingLink($taskData['url'], $id);
                $updateData['url'] = $tracking['tracking_url'];
            } else {
                $updateData['url'] = $taskData['url'] ?? $task->url;
            }

            $task->update($updateData);

            // Relations
            $task->categories()->sync($taskData['categories'] ?? []);
            $task->localities()->sync($taskData['localities'] ?? []);
            $task->occupations()->sync($taskData['occupations'] ?? []);

            DB::commit();
            $result['success'] = true;
            $result['message'] = 'Campagne mise à jour avec succès';
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
        $task = Task::find($id);

        if (!$task) {
            return ['success' => false, 'message' => 'Tâche non trouvée'];
        }

        DB::beginTransaction();

        try {
            // Récupération des IDs liés
            $taskLocalities  = $task->localities->pluck('id')->toArray();
            $taskOccupations = $task->occupations->pluck('id')->toArray();
            $taskCategories  = $task->categories->pluck('id')->toArray();

            //dd($taskCategories);

            // Diffuseurs correspondant à tous les critères
            /* $eligible = User::whereHas(
                'roles',
                fn($q) =>
                $q->where('typerole', 'DIFFUSEUR')
            )
                ->where('enabled', true)
                ->whereHas(
                    'locality',
                    fn($q) =>
                    $q->whereIn('id', $taskLocalities)
                )
                ->whereHas(
                    'occupation',
                    fn($q) =>
                    $q->whereIn('id', $taskOccupations)
                )
                ->whereHas(
                    'categories',
                    fn($q) =>
                    $q->whereIn('category_id', $taskCategories)
                )
                ->get(); */

            $eligible = User::whereHas(
                'roles',
                fn($q) =>
                $q->where('typerole', 'DIFFUSEUR')
            )
                ->where('enabled', true)
                ->where(function ($query) use ($taskLocalities, $taskOccupations, $taskCategories) {

                    $query->whereHas(
                        'locality',
                        fn($q) =>
                        $q->whereIn('id', $taskLocalities)
                    )
                        ->orWhereHas(
                            'occupation',
                            fn($q) =>
                            $q->whereIn('id', $taskOccupations)
                        )
                        ->orWhereHas(
                            'categories',
                            fn($q) =>
                            $q->whereIn('category_id', $taskCategories)
                        );
                })
                ->get();

            //dd($eligible);

            if ($eligible->isEmpty()) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Aucun diffuseur ne correspond aux critères.'];
            }

            // Exclure diffuseurs avec >= 6 tâches
            $eligible = $eligible->filter(function ($agent) {
                return Assignment::where('agent_id', $agent->id)
                    ->whereIn('status', ['PENDING', 'ASSIGNED'])
                    ->count() < 6;
            });

            //dd($eligible);

            if ($eligible->isEmpty()) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Tous les diffuseurs ont déjà 6 tâches.'];
            }

            // Calcul nombre de jours (min 1)
            $days = Carbon::parse($task->startdate)->diffInDays(Carbon::parse($task->enddate));
            $days = max(1, $days);

            //dd($days);

            // Vues journalières nécessaires
            $dailyRequired = max(1, floor($task->total_views_estimated / $days));

            //dd($dailyRequired);

            // Préparer agents
            $agents = $eligible->map(fn($a) => [
                'model' => $a,
                'vues'  => $a->vuesmoyen ?? 0
            ])->toArray();

            //dd($agents);

            // Random Greedy search
            $finalAgents = [];
            $attempts = 15;

            while ($attempts--) {
                shuffle($agents);
                $sum = 0;
                $selected = [];

                foreach ($agents as $a) {
                    if ($sum >= $dailyRequired) break;

                    $sum += $a['vues'];
                    $selected[] = $a['model'];
                }

                if ($sum >= $dailyRequired) {
                    $finalAgents = $selected;
                    break;
                }
            }

            //dd($finalAgents);

            if (empty($finalAgents)) {
                DB::rollBack();
                return ['success' => false, 'message' => 'Impossible de trouver une combinaison suffisante de diffuseurs.'];
            }

            // Création des assignments
            foreach ($finalAgents as $agent) {
                Assignment::create([
                    'id' => $this->getId(),
                    'task_id' => $task->id,
                    'agent_id' => $agent->id,
                    'assigner_id' => $validateurId,
                    'assignment_date' => now(),
                    'status' => Util::ASSIGNMENTS_STATUSES['ASSIGNED'],
                    'vues' => 0,
                    'expected_views' => $agent->vuesmoyen,
                    'gain' => 0,
                    'expected_gain' => $agent->vuesmoyen,
                ]);
            }

            // Mise à jour tâche
            $task->update([
                'status' => Util::TASKS_STATUSES["ACCEPTED"],
                'validation_date' => now(),
                'validateur_id' => $validateurId,
            ]);

            DB::commit();

            return ['success' => true, 'message' => 'Tâche approuvée et assignée avec succès'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
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
