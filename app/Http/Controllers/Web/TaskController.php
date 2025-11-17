<?php

namespace App\Http\Controllers\Web;

use App\Consts\Util;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Models\Locality;
use App\Models\Occupation;
use App\Services\TaskService;
use App\Services\AssignmentService;
use App\Services\TrackingService;
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    use Utils;

    protected $taskService;
    protected $assignmentService;
    protected $trackingService;

    public function __construct(TaskService $taskService, TrackingService $trackingService, AssignmentService $assignmentService = null)
    {
        $this->taskService = $taskService;
        $this->assignmentService = $assignmentService;
        $this->trackingService = $trackingService;
    }

    /**
     * Affiche la liste des campagnes
     */
    public function tasksGet(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $blade = "";
        $tasks = [];

        // Récupérer les paramètres de filtre
        $filters = [
            'status' => $request->input('filtre_status'),
            'client_id' => $request->input('filtre_client'),
            'category_id' => $request->input('filtre_category'),
            'start_date' => $request->input('filtre_start_date'),
            'end_date' => $request->input('filtre_end_date'),
        ];

        // Stocker les valeurs des filtres dans viewData
        foreach ($filters as $key => $value) {
            if (!empty($value)) {
                $viewData['filtre_' . str_replace('_id', '', $key)] = $value;
            }
        }

        // Récupérer les campagnes selon le profil de l'utilisateur
        switch ($request->session()->get('userprofile')) {
            case "ADMIN":
                $tasks = !empty(array_filter($filters)) ?
                    $this->taskService->getTasks($filters) :
                    $this->taskService->getAllTasks();
                $blade = "admin_tasks";
                break;
            case "ANNONCEUR":
                $filters['client_id'] = $request->session()->get('userid');
                $tasks = $this->taskService->getTasks($filters);
                $blade = "client_tasks";
                break;
            case "DIFFUSEUR":
                $tasks = $this->taskService->getAgentTasks($request->session()->get('userid'));
                $blade = "agent_tasks";
                break;
        }

        // Récupérer les statistiques
        $viewData['taskStats'] = $this->taskService->getTaskStats();

        // Si c'est un admin, récupérer la liste des clients (annonceurs) pour le filtre
        if ($request->session()->get('userprofile') == "ADMIN") {
            $viewData['clients'] = User::select('users.*')
                ->join('role_user', 'users.id', '=', 'role_user.user_id')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('roles.typerole', 'ANNONCEUR')
                ->where('users.enabled', true)
                ->orderBy('users.lastname')
                ->orderBy('users.firstname')
                ->get();
        }

        // Récupérer les catégories pour le filtre
        $viewData['categories'] = Category::where('enabled', true)
            ->orderBy('name')
            ->get();

        $viewData["tasks"] = $tasks;
        $this->setViewData($request, $viewData);

        return view('admin.' . $blade, [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Campagnes',
            'pagetilte' => 'Campagnes',
            'pagecardtilte' => 'Liste des campagnes',
        ]);
    }

    /**
     * Affiche le formulaire d'une campagne (nouveau ou existant)
     */
    public function taskGet(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $viewData["title"] = "Nouvelle campagne";
        $viewData["subtitle"] = "Veuillez bien renseigner les informations relatives à la nouvelle campagne";

        // Charger les données nécessaires pour le formulaire
        $viewData["categories"] = Category::where('enabled', true)->orderBy('name')->get();
        $viewData["localities"] = Locality::where('type', 2)->orderBy('name')->get();
        $viewData["occupations"] = Occupation::where('enabled', true)->orderBy('name')->get();

        // Selon l'ID, on affiche un nouveau formulaire ou une campagne existante
        switch ($id) {
            case "new":
                $viewData["task"] = new Task();
                break;
            default:
                $viewData["stats"] = $this->trackingService->getTaskStatistics($id);
                // Récupérer les détails de la campagne
                $task = $this->taskService->getTaskWithRelations($id);
                if (!$task) {
                    $viewData["task"] = new Task();
                    $alert["type"] = "danger";
                    $alert["message"] = "Campagne introuvable";
                } else {
                    $viewData["task"] = $task;
                    $viewData["title"] = "Détails de la campagne";
                    $viewData["subtitle"] = "Informations relatives à la campagne";

                    // Récupérer les assignations liées à cette campagne
                    if ($this->assignmentService) {
                        $viewData["assignments"] = $this->assignmentService->getAssignmentsByTasks([$id]);
                    } else {
                        $viewData["assignments"] = [];
                    }
                }
                break;
        }

        // S'assurer que les statistiques par appareil existent
        if (!isset($viewData["stats"]["devices"]) || !is_array($viewData["stats"]["devices"])) {
            $viewData["stats"]["devices"] = [
                "desktop" => 0,
                "mobile" => 0,
                "tablet" => 0,
                "unknown" => 0
            ];
        }

        // Valeurs par défaut pour les métriques avancées
        $viewData["stats"]["total_views"] = $viewData["stats"]["total_views"] ?? 0;
        $viewData["stats"]["unique_clicks"] = $viewData["stats"]["unique_clicks"] ?? 0;
        $viewData["stats"]["click_rate"] = $viewData["stats"]["click_rate"] ?? 0;
        $viewData["stats"]["conversion_rate"] = $viewData["stats"]["conversion_rate"] ?? 0;
        $viewData["stats"]["avg_time"] = $viewData["stats"]["avg_time"] ?? 0;
        $viewData["stats"]["engagement_rate"] = $viewData["stats"]["engagement_rate"] ?? 0;
        $viewData["stats"]["geography"] = $viewData["stats"]["geography"] ?? [];

        // DAILY DATA (évolution des vues & clics)
        if (
            isset($viewData["stats"]["daily_data"]) &&
            is_array($viewData["stats"]["daily_data"])
        ) {
            $daily = $viewData["stats"]["daily_data"];

            $viewData["stats"]["daily_data"] = [
                "dates"  => $daily["dates"]  ?? [],
                "views"  => $daily["views"]  ?? [],
                "clicks" => $daily["clicks"] ?? [],
            ];
        } else {
            // Si pas de données → tableau vide (pas de fallback aléatoire)
            $viewData["stats"]["daily_data"] = [
                "dates"  => [],
                "views"  => [],
                "clicks" => [],
            ];
        }

        // WEEKDAY DATA (répartition par jour de la semaine)
        if (
            isset($viewData["stats"]["weekday_data"]) &&
            is_array($viewData["stats"]["weekday_data"])
        ) {
            $weekday = $viewData["stats"]["weekday_data"];

            // Réorganisation propre selon l'ordre standard
            $viewData["stats"]["weekday_data"] = [
                "Lun" => $weekday["Lun"] ?? 0,
                "Mar" => $weekday["Mar"] ?? 0,
                "Mer" => $weekday["Mer"] ?? 0,
                "Jeu" => $weekday["Jeu"] ?? 0,
                "Ven" => $weekday["Ven"] ?? 0,
                "Sam" => $weekday["Sam"] ?? 0,
                "Dim" => $weekday["Dim"] ?? 0,
            ];
        } else {
            $viewData["stats"]["weekday_data"] = [
                "Lun" => 0,
                "Mar" => 0,
                "Mer" => 0,
                "Jeu" => 0,
                "Ven" => 0,
                "Sam" => 0,
                "Dim" => 0,
            ];
        }

        $this->setViewData($request, $viewData);

        // Déterminer la vue à utiliser (nouvelle campagne ou détails)
        $view = ($id === "new") ? 'admin.campaigns.create' : 'admin.campaigns.show';

        // Dans la méthode taskGet du TaskController
        return view($view, [
            'alert' => $alert,
            'viewData' => $viewData,
            'task' => $viewData["task"],  // Ajoutez cette ligne
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin',
            'pagetilte' => $viewData["title"],
            'pagecardtilte' => '',
        ]);
    }

    /**
     * Traite le formulaire de campagne (création ou mise à jour)
     */
    public function taskPost(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        // Validation différente selon type de formulaire (ancien ou nouveau)
        if ($request->has('media_type')) {
            // Nouveau formulaire avec multiples localités et occupations
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
                // Traitement des fichiers médias
                $filesData = [];

                if ($request->hasFile('campaign_files')) {
                    $uploadDir = public_path('uploads/campaigns');

                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    foreach ($request->file('campaign_files') as $index => $uploadedFile) {
                        $fileName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', strtolower($uploadedFile->getClientOriginalName()));
                        $filePath = $uploadedFile->storeAs('campaigns', $fileName, 'public');

                        $filesData[] = [
                            'name' => $fileName,
                            'original_name' => $uploadedFile->getClientOriginalName(),
                            'mime' => $uploadedFile->getMimeType(),
                            'size' => $uploadedFile->getSize(),
                            'path' => 'storage/' . $filePath
                        ];
                    }
                }

                // Préparation des données de la campagne
                $taskData = [
                    'name' => $request->name,
                    'descriptipon' => $request->description ?? '',
                    'budget' => $request->budget,
                    'startdate' => $request->startdate,
                    'enddate' => $request->enddate,
                    'media_type' => $request->media_type,
                    'url' => $request->url,
                    'legend' => $request->legend,
                    'client_id' => $request->session()->get('userid'),
                    'categories' => $request->categories ?? [],
                    'localities' => $request->localities,
                    'occupations' => $request->occupations,
                    'files' => !empty($filesData) ? json_encode($filesData) : null
                ];

                if ($id == 'new') {
                    $result = $this->taskService->createTask($taskData);
                } else {
                    $result = $this->taskService->updateTask($id, $taskData);
                }

                if (!$result['success']) {
                    throw new \Exception($result['message']);
                }

                $alert = [
                    'message' => ($id == 'new') ? 'Campagne créée avec succès' : 'Campagne mise à jour avec succès',
                    'type' => 'success'
                ];

                return redirect()->route('admin.tasks')->with($alert);
            } catch (\Exception $e) {
                $alert = [
                    'message' => 'Erreur: ' . $e->getMessage(),
                    'type' => 'danger'
                ];

                return redirect()->back()->withInput()->with($alert);
            }
        } else {
            // Ancien formulaire (compatibilité)
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
                return redirect(config('app.url') . '/admin/tasks')->with($alert);
            } else {
                $alert = [
                    'message' => $result['message'],
                    'type' => 'danger'
                ];

                // En cas d'erreur, recharger la page avec les données
                return $this->taskGet($request, $id);
            }
        }
    }

    /**
     * Approuve une campagne
     */
    public function approveTask(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $result = $this->taskService->approveTask($id, $request->session()->get('userid'));

        if ($result['success']) {
            $alert = [
                'message' => 'Campagne approuvée avec succès',
                'type' => 'success'
            ];
        } else {
            $alert = [
                'message' => $result['message'],
                'type' => 'danger'
            ];
        }

        return redirect(config('app.url') . '/admin/tasks')->with($alert);
    }

    /**
     * Rejette une campagne
     */
    public function rejectTask(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $result = $this->taskService->rejectTask(
            $id,
            $request->session()->get('userid'),
            $request->rejection_reason ?? $request->reason
        );

        if ($result['success']) {
            $alert = [
                'message' => 'Campagne rejetée',
                'type' => 'success'
            ];
        } else {
            $alert = [
                'message' => $result['message'],
                'type' => 'danger'
            ];
        }

        return redirect(config('app.url') . '/admin/tasks')->with($alert);
    }

    /**
     * Supprime une campagne
     */
    public function deleteTask(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $result = $this->taskService->deleteTask($id);

        if ($result['success']) {
            $alert = [
                'message' => 'Campagne supprimée avec succès',
                'type' => 'success'
            ];
        } else {
            $alert = [
                'message' => $result['message'],
                'type' => 'danger'
            ];
        }

        return redirect(config('app.url') . '/admin/tasks')->with($alert);
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
                'message' => 'Campagne non trouvée'
            ]);
        }

        // Vérifier que la tâche appartient à l'utilisateur actuel (si c'est un annonceur)
        if ($request->session()->get('userprofile') === 'ANNONCEUR' && $task->client_id !== $request->session()->get('userid')) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'êtes pas autorisé à accéder à cette campagne'
            ]);
        }

        $assignments = $this->assignmentService->getAssignmentsByTasks([$id]);

        return response()->json([
            'success' => true,
            'task' => $task,
            'assignments' => $assignments
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
