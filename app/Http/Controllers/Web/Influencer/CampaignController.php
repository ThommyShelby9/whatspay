<?php
// File: app/Http/Controllers/Web/Influencer/CampaignController.php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use App\Services\AssignmentService;
use App\Services\CategoryService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    use Utils;

    protected $taskService;
    protected $assignmentService;
    protected $categoryService;

    public function __construct(
        TaskService $taskService,
        AssignmentService $assignmentService,
        CategoryService $categoryService
    ) {
        $this->taskService = $taskService;
        $this->assignmentService = $assignmentService;
        $this->categoryService = $categoryService;
    }

    public function available(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer les campagnes disponibles
        $viewData["availableTasks"] = $this->assignmentService->getAvailableAgentTasks($userId);

        $viewData["categories"] = $this->categoryService->getAllCategories();

        $this->setViewData($request, $viewData);

        return view('influencer.campaigns.available', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Campagnes disponibles',
            'pagetilte' => 'Campagnes disponibles',
            'pagecardtilte' => 'Découvrez les opportunités'
        ]);
    }

    public function accepted(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer les campagnes assignées
        $viewData["assignments"] = $this->assignmentService->getAgentAssignments($userId);

        $this->setViewData($request, $viewData);

        return view('influencer.campaigns.accepted', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Mes Missions',
            'pagetilte' => 'Mes Missions',
            'pagecardtilte' => 'Campagnes en cours'
        ]);
    }

    public function accepte(Request $request, string $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer les détails de la mission
        $assignment = $this->assignmentService->getAssignmentById($id);

        // Vérifier que la mission appartient bien au diffuseur
        if (!$assignment || $assignment->agent_id != $userId) {
            return redirect()->route('influencer.campaigns.assigned')
                ->with('type', 'danger')
                ->with('message', 'Mission non trouvée ou non autorisée');
        }

        // Ne pas accepter une mission déjà acceptée ou refusée
        if ($assignment->status == 'PENDING') {
            return back()->with('type', 'warning')->with('message', 'Cette mission a déjà été traitée.');
        }

        // Accepter la mission
        $assignment->update([
            'status' => 'PENDING',
            'response_date' => now(),
        ]);

        $viewData['assignments'] = $this->assignmentService->getClientAssignments($userId);

        $this->setViewData($request, $viewData);

        return view('influencer.campaigns.accepted', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Mes Missions',
            'pagetilte' => 'Mes Missions',
            'pagecardtilte' => 'Campagnes en cours'
        ]);
    }

    public function show(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer les détails de la mission
        $assignment = $this->assignmentService->getAssignmentById($id);

        // Vérifier que la mission appartient bien au diffuseur
        if (!$assignment || $assignment->agent_id != $userId) {
            return redirect()->route('influencer.campaigns.assigned')
                ->with('type', 'danger')
                ->with('message', 'Mission non trouvée ou non autorisée');
        }

        $viewData["assignment"] = $assignment;

        $this->setViewData($request, $viewData);

        return view('influencer.campaigns.show', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Détails Mission',
            'pagetilte' => 'Détails de la mission',
            'pagecardtilte' => 'Informations sur la campagne'
        ]);
    }

    public function submit(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer les détails de la mission
        $assignment = $this->assignmentService->getAssignmentById($id);

        // Vérifier que la mission appartient bien au diffuseur
        if (!$assignment || $assignment->agent_id != $userId) {
            return redirect()->route('influencer.campaigns.assigned')
                ->with('type', 'danger')
                ->with('message', 'Mission non trouvée ou non autorisée');
        }

        $viewData["assignment"] = $assignment;

        $this->setViewData($request, $viewData);

        return view('influencer.campaigns.submit', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Soumettre Résultats',
            'pagetilte' => 'Soumettre les résultats',
            'pagecardtilte' => 'Finaliser la campagne'
        ]);
    }

    // Dans App\Http\Controllers\Web\Influencer\CampaignController.php

    public function storeSubmission(Request $request, $id)
    {
        $userId = $request->session()->get('userid');

        // Valider les données soumises
        $request->validate([
            'vues' => 'required|numeric|min:0',
            'files' => 'required|file|max:15000',
        ]);

        $submissionData = [
            'agent_id' => $userId,
            'vues' => $request->input('vues'),
            'files' => $request->input('files'),
        ];

        $result = $this->assignmentService->submitResult($id, $submissionData);

        if ($result['success']) {
            return redirect()->route('influencer.campaigns.accepted')
                ->with('type', 'success')
                ->with('message', 'Résultats soumis avec succès');
        } else {
            return redirect()->back()
                ->with('type', 'danger')
                ->with('message', $result['message']);
        }
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
