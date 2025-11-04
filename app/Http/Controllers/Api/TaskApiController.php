<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class TaskApiController extends Controller
{
    use Utils;
    
    protected $taskService;
    
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    
    public function index(Request $request)
    {
        try {
            // Filtres optionnels
            $filters = [
                'status' => $request->status,
                'client_id' => $request->client_id,
                'category_id' => $request->category_id,
            ];
            
            $tasks = $this->taskService->getTasks($filters);
            
            return response()->json([
                'error' => false,
                'tasks' => $tasks
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des tâches: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            
            if (!$task) {
                return response()->json([
                    'error' => true,
                    'message' => 'Tâche non trouvée'
                ], 404);
            }
            
            return response()->json([
                'error' => false,
                'task' => $task
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération de la tâche: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|max:255',
                'description' => 'required',
                'budget' => 'required|numeric|min:1000',
                'startdate' => 'required|date',
                'enddate' => 'required|date',
                'files' => 'required',
                'client_id' => 'required',
            ]);
            
            $taskData = $request->all();
            $result = $this->taskService->createTask($taskData);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Tâche créée avec succès',
                    'task_id' => $result['task_id']
                ], 201);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la création de la tâche: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            
            if (!$task) {
                return response()->json([
                    'error' => true,
                    'message' => 'Tâche non trouvée'
                ], 404);
            }
            
            $taskData = $request->all();
            $result = $this->taskService->updateTask($id, $taskData);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Tâche mise à jour avec succès'
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la mise à jour de la tâche: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            
            if (!$task) {
                return response()->json([
                    'error' => true,
                    'message' => 'Tâche non trouvée'
                ], 404);
            }
            
            $result = $this->taskService->deleteTask($id);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Tâche supprimée avec succès'
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la suppression de la tâche: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function approve(Request $request, $id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            
            if (!$task) {
                return response()->json([
                    'error' => true,
                    'message' => 'Tâche non trouvée'
                ], 404);
            }
            
            $result = $this->taskService->approveTask($id, $request->validateur_id);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Tâche approuvée avec succès'
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de l\'approbation de la tâche: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function reject(Request $request, $id)
    {
        try {
            $task = $this->taskService->getTaskById($id);
            
            if (!$task) {
                return response()->json([
                    'error' => true,
                    'message' => 'Tâche non trouvée'
                ], 404);
            }
            
            $result = $this->taskService->rejectTask($id, $request->validateur_id, $request->reason);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Tâche rejetée avec succès'
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors du rejet de la tâche: ' . $e->getMessage()
            ], 500);
        }
    }
}