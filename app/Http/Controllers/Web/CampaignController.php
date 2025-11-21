<?php
// File: app/Http/Controllers/Web/Announcer/CampaignController.php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use App\Services\CategoryService;
use App\Services\AssignmentService;
use App\Services\TrackingService;
use App\Services\MediaService;
use App\Traits\Utils;
use App\Models\Locality;
use App\Models\Occupation;
use App\Services\UserService;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignController extends Controller
{
    use Utils;

    protected $taskService;
    protected $categoryService;
    protected $assignmentService;
    protected $trackingService;
    protected $mediaService;
    protected $walletService;
    protected $userService;

    public function __construct(
        TaskService $taskService,
        CategoryService $categoryService,
        AssignmentService $assignmentService,
        TrackingService $trackingService,
        MediaService $mediaService,
        UserService $userService,
    ) {
        $this->taskService = $taskService;
        $this->categoryService = $categoryService;
        $this->assignmentService = $assignmentService;
        $this->trackingService = $trackingService;
        $this->mediaService = $mediaService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer les filtres depuis la requête
        $filters = [
            'status' => $request->get('filtre_status'),
            'category_id' => $request->get('filtre_category'),
            'start_date' => $request->get('filtre_start_date'),
            'end_date' => $request->get('filtre_end_date'),
            'client_id' => $userId, // obligatoire pour récupérer seulement ses campagnes
        ];

        // Récupérer les campagnes filtrées
        $viewData["campaigns"] = $this->taskService->getTasks($filters);

        // Catégories pour le filtre
        $viewData["categories"] = $this->categoryService->getAllCategories();

        // Comptage des affectations pour chaque campagne
        $taskIds = $viewData["campaigns"]->pluck('id')->toArray();

        $assignmentCounts = [];
        if (!empty($taskIds)) {
            $assignments = $this->assignmentService->getAssignmentsByTasks($taskIds);
            foreach ($assignments as $assignment) {
                if (!isset($assignmentCounts[$assignment->task_id])) {
                    $assignmentCounts[$assignment->task_id] = 0;
                }
                $assignmentCounts[$assignment->task_id]++;
            }
        }
        $viewData["assignmentCounts"] = $assignmentCounts;

        $this->setViewData($request, $viewData);

        return view('annonceur.campaigns.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Mes Campagnes',
            'pagetilte' => 'Mes Campagnes',
            'pagecardtilte' => 'Liste de mes campagnes'
        ]);
    }

    public function show(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Get campaign details
        $campaign = $this->taskService->getTaskById($id);

        if (!$campaign || $campaign->client_id != $userId) {
            return redirect()->route('announcer.campaigns.index')
                ->with('type', 'danger')
                ->with('message', 'Campagne non trouvée ou non autorisée');
        }

        // S'assurer que media_type existe
        if (!isset($campaign->media_type)) {
            $campaign->media_type = null;
        }

        // Informations de base
        $viewData["campaign"] = $campaign;
        $viewData["categories"] = $this->categoryService->getCategoriesByTask($id);
        $viewData["assignments"] = $this->assignmentService->getAssignmentsByTasks([$id]);
        $viewData["stats"] = $this->trackingService->getTaskStatistics($id);

        // Récupération et traitement des médias
        try {
            $mediaFiles = json_decode($campaign->files ?? '[]', true);

            // Si mediaFiles n'est pas un tableau ou est null
            if (!is_array($mediaFiles) || $mediaFiles === null) {
                $mediaFiles = [];
            }

            // Si nous avons un MediaService, on peut récupérer les médias directement
            if (empty($mediaFiles) && isset($this->mediaService)) {
                $mediaFiles = $this->mediaService->getTaskMedia($id);

                // Transformer le résultat si c'est une collection Eloquent
                if ($mediaFiles instanceof \Illuminate\Database\Eloquent\Collection) {
                    $mediaFiles = $mediaFiles->toArray();
                }
            }

            // Ajouter les URLs complètes pour chaque média si nécessaire
            foreach ($mediaFiles as &$media) {
                if (is_array($media) && isset($media['name']) && !isset($media['url'])) {
                    $media['url'] = asset('storage/uploads/' . $media['name']);
                } elseif (is_array($media) && isset($media['file_name']) && !isset($media['url'])) {
                    $media['url'] = asset('storage/uploads/' . $media['file_name']);
                }
            }

            $viewData["mediaFiles"] = $mediaFiles;
            //dd($mediaFiles);
        } catch (\Exception $e) {
            // En cas d'erreur, initialiser avec un tableau vide
            $viewData["mediaFiles"] = [];
        }

        // Récupération des localités
        $localities = $campaign->localities ?? collect();
        if ($localities->isEmpty() && !empty($campaign->locality_id)) {
            $localities = collect([Locality::find($campaign->locality_id)]);
        }
        $viewData["localities"] = $localities;

        // Récupération des occupations
        $occupations = $campaign->occupations ?? collect();
        if ($occupations->isEmpty() && !empty($campaign->occupation_id)) {
            $occupations = collect([Occupation::find($campaign->occupation_id)]);
        }
        $viewData["occupations"] = $occupations;

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

        $this->setViewData($request, $viewData);

        return view('annonceur.campaigns.show', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Détails Campagne',
            'pagetilte' => 'Détails Campagne',
            'pagecardtilte' => 'Informations sur la campagne'
        ]);
    }

    public function create(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        // Récupération des données nécessaires pour les sélecteurs
        $viewData["categories"] = $this->categoryService->getAllCategories();
        $viewData["localities"] = Locality::where('type', 2)->orderBy('name')->get();
        $viewData["occupations"] = Occupation::where('enabled', true)->orderBy('name')->get();

        $this->setViewData($request, $viewData);

        return view('annonceur.campaigns.create', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Nouvelle Campagne',
            'pagetilte' => 'Nouvelle Campagne',
            'pagecardtilte' => 'Créer une nouvelle campagne'
        ]);
    }

    public function store(Request $request)
    {
        $userId = $request->session()->get('userid');

        // Validation des données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'descriptipon' => 'nullable|string',

            'media_type' => 'required|in:image,image_link,text,video',
            'url' => 'nullable|url',

            'campaign_files' => 'nullable',
            'campaign_files.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',

            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',

            'budget' => 'required|numeric|min:1000',

            'localities' => 'required|array',
            'localities.*' => 'exists:localities,id',

            'occupations' => 'required|array',
            'occupations.*' => 'exists:occupations,id',

            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',

            'legend' => 'required|string',
        ]);

        // Validation conditionnelle
        if (in_array($request->media_type, ['image', 'video'])) {
            $request->validate([
                'campaign_files' => 'required',
            ]);
        }

        if ($request->media_type === 'image_link') {
            $request->validate([
                'url' => 'required|url',
            ]);
        }

        // Traitement des fichiers uploadés
        $files = [];
        if ($request->hasFile('campaign_files')) {
            foreach ($request->file('campaign_files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads', $fileName, 'public');
                $files[] = ['name' => $fileName, 'path' => $filePath];
            }
        }

        $taskData = [
            'name' => $request->input('name'),
            'descriptipon' => $request->input('descriptipon'),
            'files' => !empty($files) ? json_encode($files) : null,
            'client_id' => $userId,
            'startdate' => $request->input('startdate'),
            'enddate' => $request->input('enddate'),
            'budget' => $request->input('budget'),
            'categories' => $request->input('categories'),
            // Nouveaux champs
            'media_type' => $request->input('media_type'),
            'url' => $request->input('url'),
            'localities' => $request->input('localities'),
            'occupations' => $request->input('occupations'),
            'legend' => $request->input('legend'),
            'view_price' => $request->input('view_price') ?? 3.5
        ];


        $result = $this->taskService->createTask($taskData);

        if ($result['success']) {
            return redirect()->route('announcer.campaigns.show', ['id' => $result['task_id']])
                ->with('type', 'success')
                ->with('message', 'Campagne créée avec succès');
        } else {
            return redirect()->back()
                ->with('type', 'danger')
                ->with('message', $result['message']);
        }
    }

    public function edit(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        $campaign = $this->taskService->getTaskById($id);

        if (!$campaign || $campaign->client_id != $userId) {
            return redirect()->route('announcer.campaigns.index')
                ->with('type', 'danger')
                ->with('message', 'Campagne non trouvée ou non autorisée');
        }

        // Ajouter des valeurs par défaut si nécessaire
        $campaign->media_type = $campaign->media_type ?? null;
        $campaign->legend = $campaign->legend ?? null;
        $campaign->url = $campaign->url ?? null;

        // Récupérer les médias
        $mediaFiles = json_decode($campaign->files ?? '[]', true);
        if (!is_array($mediaFiles)) $mediaFiles = [];
        foreach ($mediaFiles as &$media) {
            if (isset($media['name']) && !isset($media['url'])) {
                $media['url'] = asset('storage/uploads/' . $media['name']);
            }
        }

        $viewData['campaign'] = $campaign;
        $viewData['mediaFiles'] = $mediaFiles;
        $viewData['categories'] = $this->categoryService->getAllCategories();
        $viewData['campaignCategories'] = $this->categoryService->getCategoriesByTask($id);
        $viewData['localities'] = \App\Models\Locality::where('type', 2)->orderBy('name')->get();
        $viewData['occupations'] = \App\Models\Occupation::where('enabled', true)->orderBy('name')->get();

        $this->setViewData($request, $viewData);

        return view('annonceur.campaigns.edit', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Modifier Campagne',
            'pagetilte' => 'Modifier Campagne',
            'pagecardtilte' => 'Modifier la campagne'
        ]);
    }

    public function update(Request $request, $id)
    {
        $userId = $request->session()->get('userid');
        $campaign = $this->taskService->getTaskById($id);

        if (!$campaign || $campaign->client_id != $userId) {
            return redirect()->route('announcer.campaigns.index')
                ->with('type', 'danger')
                ->with('message', 'Campagne non trouvée ou non autorisée');
        }

        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'descriptipon' => 'nullable|string',
            'media_type' => 'required|in:image,image_link,text,video',
            'url' => 'nullable|url',
            'campaign_files.*' => 'file|mimes:jpg,jpeg,png,mp4,mov,avi|max:20480',
            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',
            'budget' => 'required|numeric|min:1000',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'localities' => 'nullable|array',
            'localities.*' => 'exists:localities,id',
            'occupations' => 'nullable|array',
            'occupations.*' => 'exists:occupations,id',
            'legend' => 'required|string',
        ]);

        $balance = $this->walletService->getBalance($userId) ?? 0;

        /* if ($request->input('budget') && $request->input('budget') !== $campaign->budget && $balance < $request->input('budget')) {
            return redirect()->route('announcer.wallet')
                ->with('type', 'danger')
                ->with('message', 'Votre solde est insuffisant, veuillez ajouter des fonds !');
        } */

        $taskData = [
            'name' => $request->input('name'),
            'descriptipon' => $request->input('descriptipon'),
            'media_type' => $request->input('media_type'),
            'url' => $request->input('url'),
            'startdate' => $request->input('startdate'),
            'enddate' => $request->input('enddate'),
            'budget' => $request->input('budget'),
            'categories' => $request->input('categories') ?? [],
            'localities' => $request->input('localities') ?? [],
            'occupations' => $request->input('occupations') ?? [],
            'legend' => $request->input('legend'),
            'campaign_files' => $request->file('campaign_files'),
            'view_price' => $request->input('view_price') ?? 3.5
        ];

        $result = $this->taskService->updateTask($id, $taskData);

        if ($result['success']) {
            return redirect()->route('announcer.campaigns.show', ['id' => $id])
                ->with('type', 'success')
                ->with('message', 'Campagne mise à jour avec succès');
        } else {
            return redirect()->back()
                ->with('type', 'danger')
                ->with('message', $result['message']);
        }
    }

    public function delete(Request $request, string $id)
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

        return redirect(config('app.url') . '/admin/announcer/campaigns')->with($alert);
    }

    private function setAlert(Request &$request, &$alert)
    {
        $alert = [
            'message' => (!empty($request->message) ? $request->message : (!empty(session('message')) ? session('message') : "")),
            'type' => (!empty($request->type) ? $request->type : (!empty(session('type')) ? session('type') : "success")),
        ];
    }

    private function isConnected()
    {
        return (Auth::viaRemember() || Auth::check());
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
