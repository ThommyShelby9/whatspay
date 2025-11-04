<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AssignmentService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class AssignmentApiController extends Controller
{
    use Utils;
    
    protected $assignmentService;
    
    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }
    
    public function index(Request $request)
    {
        try {
            // Filtres optionnels
            $filters = [
                'status' => $request->status,
                'task_id' => $request->task_id,
                'agent_id' => $request->agent_id,
            ];
            
            $assignments = $this->assignmentService->getAssignments($filters);
            
            return response()->json([
                'error' => false,
                'assignments' => $assignments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des affectations: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $assignment = $this->assignmentService->getAssignmentById($id);
            
            if (!$assignment) {
                return response()->json([
                    'error' => true,
                    'message' => 'Affectation non trouvée'
                ], 404);
            }
            
            return response()->json([
                'error' => false,
                'assignment' => $assignment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération de l\'affectation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $request->validate([
                'task_id' => 'required',
                'agent_id' => 'required',
                'assigner_id' => 'required',
            ]);
            
            $assignmentData = $request->all();
            $result = $this->assignmentService->createAssignment($assignmentData);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Affectation créée avec succès',
                    'assignment_id' => $result['assignment_id']
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
                'message' => 'Erreur lors de la création de l\'affectation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $assignment = $this->assignmentService->getAssignmentById($id);
            
            if (!$assignment) {
                return response()->json([
                    'error' => true,
                    'message' => 'Affectation non trouvée'
                ], 404);
            }
            
            $assignmentData = $request->all();
            $result = $this->assignmentService->updateAssignment($id, $assignmentData);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Affectation mise à jour avec succès'
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
                'message' => 'Erreur lors de la mise à jour de l\'affectation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $assignment = $this->assignmentService->getAssignmentById($id);
            
            if (!$assignment) {
                return response()->json([
                    'error' => true,
                    'message' => 'Affectation non trouvée'
                ], 404);
            }
            
            $result = $this->assignmentService->deleteAssignment($id);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Affectation supprimée avec succès'
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
                'message' => 'Erreur lors de la suppression de l\'affectation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function submit(Request $request, $id)
    {
        try {
            $assignment = $this->assignmentService->getAssignmentById($id);
            
            if (!$assignment) {
                return response()->json([
                    'error' => true,
                    'message' => 'Affectation non trouvée'
                ], 404);
            }
            
            $request->validate([
                'vues' => 'required|numeric|min:1',
                'files' => 'required',
                'agent_id' => 'required',
            ]);
            
            $result = $this->assignmentService->submitResult($id, $request->all());
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Résultat soumis avec succès'
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
                'message' => 'Erreur lors de la soumission du résultat: ' . $e->getMessage()
            ], 500);
        }
    }
    

    public function getAvailableAgents(Request $request, $taskId)
    {
        try {
            $filters = [
                'category_id' => $request->category_id,
                'max_assignments' => $request->has('max_assignments') ? $request->max_assignments : 3,
            ];
            
            $agents = $this->assignmentService->getAvailableAgentsForTask($taskId, $filters);
            
            return response()->json([
                'error' => false,
                'agents' => $agents
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des agents disponibles: ' . $e->getMessage()
            ], 500);
        }
    }
}