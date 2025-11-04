<?php

namespace App\Http\Controllers\Web;

use App\Consts\Util;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Task;
use App\Services\TaskService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    use Utils;
    
    protected $taskService;
    
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }
    
    public function tasksGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $blade = "";
        $tasks = [];

        switch ($request->session()->get('userprofile')) {
            case "ADMIN":
                $tasks = $this->taskService->getAllTasks();
                $blade = "admin_tasks";
                break;
            case "ANNONCEUR":
                $tasks = $this->taskService->getClientTasks($request->session()->get('userid'));
                $blade = "client_tasks";
                break;
            case "DIFFUSEUR":
                $tasks = $this->taskService->getAgentTasks($request->session()->get('userid'));
                $blade = "agent_tasks";
                break;
        }

        $viewData["tasks"] = $tasks;
        $this->setViewData($request, $viewData);
        
        return view('admin.' . $blade, [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Tâches', 
            'pagecardtilte' => 'Liste des tâches',
        ]);
    }
    
    public function taskGet(Request $request, $id)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $viewData["title"] = "Nouvelle tâche";
        $viewData["subtitle"] = "Veuillez bien renseigner les informations relatives à la nouvelle tâche";

        switch ($id) {
            case "new":
                $viewData["task"] = new Task();
                break;
            default:
                $task = $this->taskService->getTaskById($id);
                if (!$task) {
                    $viewData["task"] = new Task();
                } else {
                    $viewData["task"] = $task;
                    $viewData["title"] = "Fiche tâche";
                    $viewData["subtitle"] = "Ci-dessous les informations relatives à la tâche";
                }
                break;
        }

        $viewData["categories"] = Category::all();
        $this->setViewData($request, $viewData);
        
        return view('admin.task', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => $viewData["title"], 
            'pagecardtilte' => '',
        ]);
    }
    
    public function taskPost(Request $request, $id)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'budget' => 'required|numeric|min:1000',
            'startdate' => 'required|date_format:d/m/Y',
            'enddate' => 'required|date_format:d/m/Y',
            'taskfiles' => 'required',
        ]);

        // Récupérer les catégories sélectionnées
        $requestData = $request->all();
        $selectedCategories = [];
        $categories = Category::all();
        foreach ($categories as $category) {
            if (!empty($requestData["c_" . $category->id])) {
                $selectedCategories[] = $category->id;
            }
        }

        $startdate = explode('/', $request->startdate);
        $startdate = $startdate[2] . '-' . $startdate[1] . '-' . $startdate[0];

        $enddate = explode('/', $request->enddate);
        $enddate = $enddate[2] . '-' . $enddate[1] . '-' . $enddate[0];

        $taskData = [
            'name' => $request->name,
            'descriptipon' => $request->description,  // Note: typo in field name preserved from original
            'files' => $request->taskfiles,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'budget' => $request->budget,
            'client_id' => $request->session()->get('userid'),
            'categories' => $selectedCategories
        ];

        if ($id == 'new') {
            $result = $this->taskService->createTask($taskData);
        } else {
            $result = $this->taskService->updateTask($id, $taskData);
        }

        if ($result['success']) {
            $alert = [
                'message' => $result['message'],
                'type' => 'success'
            ];
            return redirect(config('app.url').'/admin/tasks')->with($alert);
        } else {
            $alert = [
                'message' => $result['message'],
                'type' => 'danger'
            ];
            
            // En cas d'erreur, recharger la page avec les données
            return $this->taskGet($request, $id);
        }
    }
    
    public function approveTask(Request $request, $id)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $result = $this->taskService->approveTask($id, $request->session()->get('userid'));

        if ($result['success']) {
            $alert = [
                'message' => 'Tâche approuvée avec succès',
                'type' => 'success'
            ];
        } else {
            $alert = [
                'message' => $result['message'],
                'type' => 'danger'
            ];
        }

        return redirect(config('app.url').'/admin/tasks')->with($alert);
    }
    
    public function rejectTask(Request $request, $id)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $result = $this->taskService->rejectTask($id, $request->session()->get('userid'), $request->reason);

        if ($result['success']) {
            $alert = [
                'message' => 'Tâche rejetée',
                'type' => 'success'
            ];
        } else {
            $alert = [
                'message' => $result['message'],
                'type' => 'danger'
            ];
        }

        return redirect(config('app.url').'/admin/tasks')->with($alert);
    }
    
    private function isConnected()
    {
        return (\Auth::viaRemember() || \Auth::check());
    }

    private function setAlert(Request &$request, &$alert)
    {
        $alert = [
            'message' => (!empty($request->message) ? $request->message : (!empty(session('message')) ? session('message') : "")),
            'type' => (!empty($request->type) ? $request->type : (!empty(session('type')) ? session('type') : "success")),
        ];
    }

    private function setViewData(Request &$request, &$viewData)
    {
        $viewData['uri'] = \Route::currentRouteName();
        $viewData['baseUrl'] = config('app.url');
        $viewData['version'] = gmdate('YmdHis');
        $viewData['user'] = ($request->session()->has('user') ? $request->session()->get('user') : "");
        $viewData['userid'] = ($request->session()->has('userid') ? $request->session()->get('userid') : "");
        $viewData['userprofile'] = ($request->session()->has('userprofile') ? $request->session()->get('userprofile') : "");
        $viewData['userrights'] = ($request->session()->has('userrights') ? (json_decode($request->session()->get('userrights'), true)) : []);
        $viewData['userfirstname'] = ($request->session()->has('userfirstname') ? $request->session()->get('userfirstname') : "");
        $viewData['userlastname'] = ($request->session()->has('userlastname') ? $request->session()->get('userlastname') : "");
    }

    /**
 * Récupère les affectations d'une tâche
 * 
 * @param Request $request
 * @param string $id ID de la tâche
 * @return \Illuminate\Http\JsonResponse
 */
public function getTaskAssignments(Request $request, $id)
{
    $task = $this->taskService->getTaskById($id);
    
    if (!$task) {
        return response()->json([
            'success' => false,
            'message' => 'Tâche non trouvée'
        ]);
    }
    
    // Vérifier que la tâche appartient à l'utilisateur actuel (si c'est un annonceur)
    if ($request->session()->get('userprofile') === 'ANNONCEUR' && $task->client_id !== $request->session()->get('userid')) {
        return response()->json([
            'success' => false,
            'message' => 'Vous n\'êtes pas autorisé à accéder à cette tâche'
        ]);
    }
    
    $assignments = $this->assignmentService->getAssignmentsByTasks([$id]);
    
    return response()->json([
        'success' => true,
        'task' => $task,
        'assignments' => $assignments
    ]);
}
}