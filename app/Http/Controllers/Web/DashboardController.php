<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use App\Services\UserService;
use App\Services\AssignmentService;
use App\Services\CategoryService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use Utils;
    
    protected $taskService;
    protected $userService;
    protected $assignmentService;
    protected $categoryService;
    
    public function __construct(
        TaskService $taskService,
        UserService $userService,
        AssignmentService $assignmentService,
        CategoryService $categoryService
    ) {
        $this->taskService = $taskService;
        $this->userService = $userService;
        $this->assignmentService = $assignmentService;
        $this->categoryService = $categoryService;
    }
    
    public function dashboardGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $userProfile = $request->session()->get('userprofile');
        $userId = $request->session()->get('userid');
        
        switch ($userProfile) {
            case "ADMIN":
                return $this->adminDashboard($request);
                break;
            case "ANNONCEUR":
                return $this->announcerDashboard($request);
                break;
            case "DIFFUSEUR":
                return $this->influencerDashboard($request);
                break;
        }

        $this->setViewData($request, $viewData);
        
        return view('admin.dashboard', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Dashboard', 
            'pagecardtilte' => 'Bienvenue sur WhatsPAY | Admin',
        ]);
    }
    
    public function adminDashboard(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        // Récupérer les statistiques pour l'admin
        $viewData["taskStats"] = $this->taskService->getTaskStats();
        $viewData["userStats"] = $this->userService->getUserStats();
        $viewData["assignmentStats"] = $this->assignmentService->getAssignmentStats();
        
        // Récupérer les 5 dernières tâches
        $viewData["recentTasks"] = $this->taskService->getRecentTasks(5);
        
        // Récupérer les 5 derniers utilisateurs inscrits
        $viewData["recentUsers"] = $this->userService->getRecentUsers(5);
        
        $this->setViewData($request, $viewData);
        
        return view('admin.dashboardget', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Dashboard Administrateur', 
            'pagecardtilte' => 'Bienvenue sur WhatsPAY | Admin',
        ]);
    }
    
public function announcerDashboard(Request $request)
{
    $viewData = []; 
    $alert = []; 
    $this->setAlert($request, $alert);
    
    $userId = $request->session()->get('userid');
    
    // Récupérer les statistiques pour l'annonceur
    try {
        $viewData["taskStats"] = $this->taskService->getClientTaskStats($userId);
    } catch (Exception $e) {
        $viewData["taskStats"] = [
            'total' => 0,
            'pending' => 0,
            'accepted' => 0,
            'paid' => 0,
            'rejected' => 0,
            'closed' => 0,
            'total_budget' => 0
        ];
    }
    
    // Récupérer les tâches récentes de l'annonceur
    try {
        $viewData["recentTasks"] = $this->taskService->getRecentClientTasks($userId, 10);
    } catch (Exception $e) {
        $viewData["recentTasks"] = [];
    }
    
    // Assignations
    try {
        $viewData["assignmentStats"] = $this->assignmentService->getClientAssignmentStats($userId);
        
        $assignmentCounts = [];
        if (!empty($viewData["recentTasks"])) {
            $taskIds = [];
            foreach ($viewData["recentTasks"] as $task) {
                $taskIds[] = $task->id;
            }
            
            if (!empty($taskIds)) {
                $assignments = $this->assignmentService->getAssignmentsByTasks($taskIds);
                foreach ($assignments as $assignment) {
                    if (isset($assignment->task_id) && !isset($assignmentCounts[$assignment->task_id])) {
                        $assignmentCounts[$assignment->task_id] = 0;
                    }
                    if (isset($assignment->task_id)) {
                        $assignmentCounts[$assignment->task_id]++;
                    }
                }
            }
        }
        $viewData["assignmentCounts"] = $assignmentCounts;
    } catch (Exception $e) {
        $viewData["assignmentStats"] = [
            'total' => 0,
            'pending' => 0,
            'accepted' => 0,
            'submitted' => 0,
            'paid' => 0,
            'rejected' => 0,
            'paid_budget' => 0,
            'pending_budget' => 0
        ];
        $viewData["assignmentCounts"] = [];
    }
    
    // Catégories
    try {
        $viewData["categories"] = $this->categoryService->getAllCategories();
    } catch (Exception $e) {
        $viewData["categories"] = [];
    }
    
    // Diffuseurs recommandés
    try {
        $viewData["recommendedAgents"] = $this->userService->getRecommendedAgents($userId, 4);
    } catch (Exception $e) {
        $viewData["recommendedAgents"] = [];
    }
    
    $this->setViewData($request, $viewData);
    
    return view('annonceur.dashboard', [
        'alert' => $alert, 
        'viewData' => $viewData, 
        'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Annonceur', 
        'pagetilte' => 'Dashboard Annonceur', 
        'pagecardtilte' => 'Bienvenue sur votre espace annonceur',
    ]);
}
    
    public function influencerDashboard(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        // Récupérer les statistiques pour le diffuseur
        $viewData["assignmentStats"] = $this->assignmentService->getAgentAssignmentStats($userId);
        
        // Récupérer les 5 dernières affectations du diffuseur
        $viewData["recentAssignments"] = $this->assignmentService->getRecentAgentAssignments($userId, 5);
        
        // Récupérer les statistiques de gains
        $viewData["earningsStats"] = $this->assignmentService->getAgentEarningsStats($userId);
        
        $this->setViewData($request, $viewData);
        
        return view('admin.agent_dashboard', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Dashboard Diffuseur', 
            'pagecardtilte' => 'Bienvenue sur WhatsPAY | Diffuseur',
        ]);
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
}