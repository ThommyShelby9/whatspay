<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Role;
use App\Models\User;
use App\Models\Task;
use App\Services\AssignmentService;
use App\Services\TaskService;
use App\Services\UserService;
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL; // Ajout de l'import URL


class DashboardAdminController extends Controller
{
    use Utils;
    
    protected $userService;
    protected $taskService;
    protected $assignmentService;
    
    public function __construct(
        UserService $userService,
        TaskService $taskService,
        AssignmentService $assignmentService
    ) {
        $this->userService = $userService;
        $this->taskService = $taskService;
        $this->assignmentService = $assignmentService;
    }
    
    public function index(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        // Statistiques pour le tableau de bord
        $viewData['userStats'] = $this->userService->getUserStats();
        $viewData['taskStats'] = $this->taskService->getTaskStats();
        $viewData['assignmentStats'] = $this->assignmentService->getAssignmentStats();
        
        // Utilisateurs récents
        $viewData['recentUsers'] = $this->userService->getRecentUsers(5);
        
        // Tâches récentes
        $viewData['recentTasks'] = $this->taskService->getRecentTasks(5);
        
        // Données de revenu mensuel pour le graphique
        $viewData['monthlyRevenue'] = $this->getMonthlyRevenue();
        
        // Données d'inscription d'utilisateurs pour le graphique
        $viewData['userRegistrations'] = $this->getUserRegistrations();
        
        $this->setViewData($request, $viewData);
        
        return view('admin.dashboardget', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin Dashboard', 
            'pagetilte' => 'Tableau de bord', 
            'pagecardtilte' => 'Aperçu général',
        ]);
    }
    
    private function getMonthlyRevenue()
    {
        $monthlyData = [];
        
        // Boucle pour les 12 derniers mois
        for ($i = 11; $i >= 0; $i--) {
            // Utiliser Carbon pour la gestion des dates
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            $monthName = $date->format('M');
            
            // Requête Eloquent sans utiliser de SQL spécifique à la base de données
            $revenue = Assignment::whereBetween('submission_date', [$startOfMonth, $endOfMonth])
                ->sum('gain') ?? 0;
            
            $monthlyData[] = [
                'month' => $monthName,
                'revenue' => $revenue
            ];
        }
        
        return $monthlyData;
    }
    
    private function getUserRegistrations()
    {
        $registrationData = [];
        
        // Récupérer les IDs des rôles une seule fois
        $announcerRole = Role::where('typerole', 'ANNONCEUR')->first();
        $influencerRole = Role::where('typerole', 'DIFFUSEUR')->first();
        
        if (!$announcerRole || !$influencerRole) {
            // Gérer le cas où les rôles n'existent pas
            return [];
        }
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();
            $monthName = $date->format('M');
            
            // Requête pour trouver les utilisateurs créés dans ce mois
            $usersInMonth = User::whereBetween('created_at', [$startOfMonth, $endOfMonth])->get();
            $userIds = $usersInMonth->pluck('id')->toArray();
            
            // Si aucun utilisateur ce mois-ci, on met des zéros
            if (empty($userIds)) {
                $registrationData[] = [
                    'month' => $monthName,
                    'announcers' => 0,
                    'influencers' => 0
                ];
                continue;
            }
            
            // Compter les annonceurs et diffuseurs avec des requêtes indépendantes de la base de données
            $announcers = DB::table('role_user')
                ->where('role_id', $announcerRole->id)
                ->whereIn('user_id', $userIds)
                ->count();
                
            $influencers = DB::table('role_user')
                ->where('role_id', $influencerRole->id)
                ->whereIn('user_id', $userIds)
                ->count();
            
            $registrationData[] = [
                'month' => $monthName,
                'announcers' => $announcers,
                'influencers' => $influencers
            ];
        }
        
        return $registrationData;
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
 * Affiche le formulaire de création d'une campagne
 */
public function createCampaign(Request $request)
{
    $viewData = []; 
    $alert = []; 
    $this->setAlert($request, $alert);
    
    if (!$this->isConnected()) {
        $url = URL::route('admin.login', [], true, config('app.url'));
        return redirect()->to($url)->with($alert);
    }
    
    // Récupérer les données nécessaires pour le formulaire
    $localities = DB::select("select * from localities where type = 2 order by localities.name asc");
    $viewData["localities"] = $localities;
    
    $viewData["occupations"] = DB::table('occupations')->orderBy('name', 'asc')->get();
    $viewData["categories"] = DB::table('categories')->orderBy('name', 'asc')->get();
    
    $this->setViewData($request, $viewData);
    
    return view('admin.campaigns.create', [
        'alert' => $alert, 
        'viewData' => $viewData, 
        'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Création de Campagne', 
        'pagetilte' => 'Nouvelle Campagne', 
        'pagecardtilte' => '',
    ]);
}

/**
 * Traite le formulaire de création d'une campagne
 */
/**
 * Traite le formulaire de création d'une campagne
 */
 public function storeCampaign(Request $request)
{
    $viewData = []; 
    $alert = []; 
    $this->setAlert($request, $alert);
    
    if (!$this->isConnected()) {
        $url = URL::route('admin.login', [], true, config('app.url'));
        return redirect()->to($url)->with($alert);
    }
    
    // Validation mise à jour pour les tableaux
    $request->validate([
        'name' => 'required|string|max:255',
        'budget' => 'required|numeric|min:1000',
        'startdate' => 'required|date',
        'enddate' => 'required|date|after_or_equal:startdate',
        'media_type' => 'required|string',
        'localities' => 'required|array', // Changé de locality_id à localities[]
        'localities.*' => 'exists:localities,id', // Valider chaque élément
        'occupations' => 'required|array', // Changé de occupation_id à occupations[]
        'occupations.*' => 'exists:occupations,id', // Valider chaque élément
        'legend' => 'required|string',
        'url' => 'nullable|url',
    ]);
    
    try {
        // Traitement des fichiers médias
        $filesData = [];
        
        // Vérifier si nous avons des fichiers dans la requête
        if ($request->hasFile('campaign_files')) {
            $uploadDir = public_path('uploads/campaigns');
            
            // Créer le répertoire s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Récupérer tous les fichiers
            foreach ($request->file('campaign_files') as $index => $uploadedFile) {
                // Générer un nom de fichier unique
                $fileName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', strtolower($uploadedFile->getClientOriginalName()));
                
                // Enregistrer le fichier dans le répertoire public
                $filePath = $uploadedFile->storeAs('campaigns', $fileName, 'public');
                
                // Ajouter les informations du fichier à notre tableau
                $filesData[] = [
                    'name' => $fileName,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime' => $uploadedFile->getMimeType(),
                    'size' => $uploadedFile->getSize(),
                    'path' => 'storage/' . $filePath
                ];
            }
        }
        
        // Création de la campagne
        $userId = $request->session()->get('userid');
        
        $taskData = [
            'name' => $request->name,
            'descriptipon' => $request->description ?? '', // Notez la faute d'orthographe dans la BD
            'budget' => $request->budget,
            'startdate' => $request->startdate,
            'enddate' => $request->enddate,
            'media_type' => $request->media_type,
            'localities' => $request->localities, // Tableau de localités
            'occupations' => $request->occupations, // Tableau de professions
            'legend' => $request->legend,
            'url' => $request->url,
            'client_id' => $userId,
            'categories' => $request->categories ?? [],
            'files' => !empty($filesData) ? json_encode($filesData) : null
        ];

        
        $result = $this->taskService->createTask($taskData);
        
        if (!$result['success']) {
            throw new \Exception($result['message']);
        }
        
        $alert['type'] = 'success';
        $alert['message'] = 'Campagne créée avec succès.';
        
        // Redirection vers la liste des campagnes
        $url = URL::route('admin.tasks', [], true, config('app.url'));
        return redirect()->to($url)->with($alert);
        
    } catch (\Exception $e) {
        $alert['type'] = 'danger';
        $alert['message'] = 'Erreur lors de la création de la campagne: ' . $e->getMessage();
        
        // Log l'erreur pour le débogage
        \Log::error('Erreur lors de la création de campagne: ' . $e->getMessage(), [
            'exception' => $e,
            'request' => $request->all()
        ]);
        
        return back()->withInput()->with($alert);
    }
}

/**
 * Affiche les détails d'une campagne
 */
public function showCampaign($id)
{
    $viewData = []; 
    $alert = []; 
    $this->setAlert($request, $alert);
    
    if (!$this->isConnected()) {
        $url = URL::route('admin.login', [], true, config('app.url'));
        return redirect()->to($url)->with($alert);
    }
    
    // Récupérer la campagne avec ses relations
    $task = Task::with(['localities', 'occupations', 'categories'])->find($id);
    
    if (!$task) {
        $alert['type'] = 'danger';
        $alert['message'] = 'Campagne introuvable';
        return redirect()->route('admin.tasks')->with($alert);
    }
    
    // Récupérer les assignations liées à cette tâche
    $assignments = DB::table('assignments')
        ->join('users', 'assignments.agent_id', '=', 'users.id')
        ->where('assignments.task_id', $id)
        ->select('assignments.*', 'users.firstname as agent_firstname', 'users.lastname as agent_lastname', 'users.email as agent_email')
        ->get();
    
    // Statistiques de la campagne
    $stats = [
        'views' => $assignments->sum('vues'),
        'clicks' => 0, // À implémenter selon votre logique
        'influencers' => $assignments->count(),
        'ctr' => '0%' // À calculer selon votre logique
    ];
    
    // Données pour les listes déroulantes du formulaire de modification
    $viewData['localities'] = DB::table('localities')->where('type', 2)->orderBy('name', 'asc')->get();
    $viewData['occupations'] = DB::table('occupations')->orderBy('name', 'asc')->get();
    $viewData['categories'] = DB::table('categories')->orderBy('name', 'asc')->get();
    
    return view('admin.campaigns.show', [
        'task' => $task,
        'assignments' => $assignments,
        'stats' => $stats,
        'viewData' => $viewData,
        'alert' => $alert,
        'title' => 'WhatsPAY | Détail Campagne',
        'version' => gmdate("YmdHis")
    ]);
}

/**
 * Met à jour une campagne existante
 */
public function updateCampaign(Request $request, $id)
{
    $viewData = []; 
    $alert = []; 
    $this->setAlert($request, $alert);
    
    if (!$this->isConnected()) {
        $url = URL::route('admin.login', [], true, config('app.url'));
        return redirect()->to($url)->with($alert);
    }
    
    // Validation
    $request->validate([
        'name' => 'required|string|max:255',
        'budget' => 'required|numeric|min:1000',
        'startdate' => 'required|date',
        'enddate' => 'required|date|after_or_equal:startdate',
        'media_type' => 'required|string',
        'localities' => 'required|array',
        'localities.*' => 'exists:localities,id',
        'occupations' => 'required|array',
        'occupations.*' => 'exists:occupations,id',
        'legend' => 'required|string',
        'url' => 'nullable|url',
    ]);
    
    try {
        // Récupérer la tâche existante
        $task = Task::find($id);
        
        if (!$task) {
            throw new \Exception('Campagne introuvable');
        }
        
        // Gérer les fichiers existants
        $existingFiles = json_decode($task->files, true) ?: [];
        $filesToKeep = [];
        
        if ($request->has('keep_files') && is_array($request->keep_files)) {
            foreach ($request->keep_files as $fileIndex) {
                if (isset($existingFiles[$fileIndex])) {
                    $filesToKeep[] = $existingFiles[$fileIndex];
                }
            }
        }
        
        // Traitement des nouveaux fichiers
        if ($request->hasFile('new_campaign_files')) {
            $uploadDir = public_path('uploads/campaigns');
            
            // Créer le répertoire s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            foreach ($request->file('new_campaign_files') as $index => $uploadedFile) {
                // Générer un nom de fichier unique
                $fileName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', strtolower($uploadedFile->getClientOriginalName()));
                
                // Enregistrer le fichier dans le répertoire public
                $filePath = $uploadedFile->storeAs('campaigns', $fileName, 'public');
                
                // Ajouter les informations du fichier à notre tableau
                $filesToKeep[] = [
                    'name' => $fileName,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime' => $uploadedFile->getMimeType(),
                    'size' => $uploadedFile->getSize(),
                    'path' => 'storage/' . $filePath
                ];
            }
        }
        
        // Préparer les données de mise à jour
        $taskData = [
            'name' => $request->name,
            'descriptipon' => $request->description ?? '',
            'budget' => $request->budget,
            'startdate' => $request->startdate,
            'enddate' => $request->enddate,
            'media_type' => $request->media_type,
            'url' => $request->url,
            'legend' => $request->legend,
            'files' => !empty($filesToKeep) ? json_encode($filesToKeep) : null
        ];
        
        // Appeler le service pour mettre à jour la tâche et ses relations
        $userId = $request->session()->get('userid');
        $taskData['client_id'] = $userId; // Pour compatibilité avec le service
        $taskData['localities'] = $request->localities;
        $taskData['occupations'] = $request->occupations;
        $taskData['categories'] = $request->categories ?? [];
        
        $result = $this->taskService->updateTask($id, $taskData);
        
        if (!$result['success']) {
            throw new \Exception($result['message']);
        }
        
        $alert['type'] = 'success';
        $alert['message'] = 'Campagne mise à jour avec succès';
        
        return redirect()->route('admin.campaigns.show', $id)->with($alert);
        
    } catch (\Exception $e) {
        $alert['type'] = 'danger';
        $alert['message'] = 'Erreur lors de la mise à jour de la campagne: ' . $e->getMessage();
        
        return back()->withInput()->with($alert);
    }
}
}