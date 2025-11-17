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

    public function __construct(
        TaskService $taskService,
        CategoryService $categoryService,
        AssignmentService $assignmentService,
        TrackingService $trackingService,
        MediaService $mediaService
    ) {
        $this->taskService = $taskService;
        $this->categoryService = $categoryService;
        $this->assignmentService = $assignmentService;
        $this->trackingService = $trackingService;
        $this->mediaService = $mediaService;
    }

    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Get filters
        $filters = [
            'status' => $request->get('filtre_status'),
            'category_id' => $request->get('filtre_category'),
            'start_date' => $request->get('filtre_start_date'),
            'end_date' => $request->get('filtre_end_date')
        ];

        // Get campaigns
        $viewData["campaigns"] = $this->taskService->getClientTasks($userId);
        $viewData["categories"] = $this->categoryService->getAllCategories();

        // Get assignment counts per campaign
        $taskIds = [];
        foreach ($viewData["campaigns"] as $campaign) {
            $taskIds[] = $campaign->id;
        }

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

        // Convertir l'objet en tableau puis en objet pour assurer que toutes les propriétés soient accessibles
        // Cela résout le problème des propriétés manquantes ou des différences de casse
        $campaignArray = $campaign->toArray();

        // S'assurer que media_type existe
        if (!isset($campaignArray['media_type'])) {
            $campaignArray['media_type'] = null;
        }

        // Reconvertir en objet pour la vue
        $campaign = (object)$campaignArray;

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
        } catch (\Exception $e) {
            // En cas d'erreur, initialiser avec un tableau vide
            $viewData["mediaFiles"] = [];
        }

        // Récupérer les informations de localité et profession
        if (!empty($campaign->locality_id)) {
            $viewData["locality"] = Locality::find($campaign->locality_id);
        } else {
            $viewData["locality"] = null;
        }

        if (!empty($campaign->occupation_id)) {
            $viewData["occupation"] = Occupation::find($campaign->occupation_id);
        } else {
            $viewData["occupation"] = null;
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

        //dd($viewData['stats']);

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
        $viewData["localities"] = Locality::where('active', true)->orderBy('name')->get();
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
            'files' => 'nullable',
            'startdate' => 'required|date',
            'enddate' => 'required|date|after_or_equal:startdate',
            'budget' => 'required|numeric|min:1000',
            'categories' => 'nullable|array',
            'media_type' => 'required|string',
            'url' => 'nullable|url',
            'locality_id' => 'required|string',
            'occupation_id' => 'required|string',
            'legend' => 'required|string',
        ]);

        // Traitement des fichiers uploadés
        $files = [];
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads', $fileName, 'public');
                $files[] = [
                    'name' => $fileName,
                    'path' => $filePath
                ];
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
            'locality_id' => $request->input('locality_id'),
            'occupation_id' => $request->input('occupation_id'),
            'legend' => $request->input('legend'),
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

        // Get campaign details
        $campaign = $this->taskService->getTaskById($id);
        if (!$campaign || $campaign->client_id != $userId) {
            return redirect()->route('announcer.campaigns.index')
                ->with('type', 'danger')
                ->with('message', 'Campagne non trouvée ou non autorisée');
        }

        // Convertir l'objet en tableau pour assurer l'accès à toutes les propriétés
        $campaignArray = $campaign->toArray();

        // Assurer que toutes les propriétés nécessaires existent
        $requiredProps = [
            'media_type' => null,
            'locality_id' => null,
            'occupation_id' => null,
            'legend' => null,
            'url' => null,
            'text' => null
        ];

        foreach ($requiredProps as $prop => $defaultValue) {
            if (!isset($campaignArray[$prop])) {
                $campaignArray[$prop] = $defaultValue;
            }
        }

        // Reconvertir en objet pour la vue
        $campaign = (object)$campaignArray;

        // Récupérer les médias de la campagne
        $mediaFiles = json_decode($campaign->files ?? '[]', true);
        if (!is_array($mediaFiles)) {
            $mediaFiles = [];
        }

        // Ajouter les URLs complètes pour chaque média si nécessaire
        foreach ($mediaFiles as &$media) {
            if (isset($media['name']) && !isset($media['url'])) {
                $media['url'] = asset('storage/uploads/' . $media['name']);
            }
        }

        $viewData["campaign"] = $campaign;
        $viewData["categories"] = $this->categoryService->getAllCategories();
        $viewData["campaignCategories"] = $this->categoryService->getCategoriesByTask($id);
        $viewData["mediaFiles"] = $mediaFiles;

        // Charger les localités et professions
        $viewData["localities"] = \App\Models\Locality::where('active', true)->orderBy('name')->get();
        $viewData["occupations"] = \App\Models\Occupation::where('enabled', true)->orderBy('name')->get();

        // DEBUG: ajouter ces informations à la vue pour faciliter le débogage
        $viewData["debug_info"] = [
            'campaign_array' => $campaignArray,
            'has_locality_id' => isset($campaign->locality_id),
            'has_occupation_id' => isset($campaign->occupation_id)
        ];

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

        // Get campaign details
        $campaign = $this->taskService->getTaskById($id);
        if (!$campaign || $campaign->client_id != $userId) {
            return redirect()->route('announcer.campaigns.index')
                ->with('type', 'danger')
                ->with('message', 'Campagne non trouvée ou non autorisée');
        }

        // Traitement des fichiers uploadés
        $mediaFiles = json_decode($campaign->files ?? '[]', true);
        if (!is_array($mediaFiles)) {
            $mediaFiles = [];
        }

        if ($request->hasFile('campaign_files')) {
            // Remplacer les anciens fichiers par les nouveaux
            $mediaFiles = [];

            foreach ($request->file('campaign_files') as $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('uploads', $fileName, 'public');

                $mediaFiles[] = [
                    'name' => $fileName,
                    'original_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getMimeType(),
                    'path' => $filePath,
                    'url' => asset('storage/' . $filePath),
                    'size' => $file->getSize()
                ];
            }
        }

        // Construction des données pour la mise à jour
        $taskData = [
            'name' => $request->input('name'),
            'descriptipon' => $request->input('descriptipon'),
            'files' => json_encode($mediaFiles),
            'startdate' => $request->input('startdate'),
            'enddate' => $request->input('enddate'),
            'budget' => $request->input('budget'),
            'categories' => $request->input('categories'),
            // Nouveaux champs
            'media_type' => $request->input('media_type'),
            'url' => $request->input('url'),
            'locality_id' => $request->input('locality_id'),
            'occupation_id' => $request->input('occupation_id'),
            'legend' => $request->input('legend')
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
