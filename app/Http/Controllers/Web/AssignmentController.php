<?php

namespace App\Http\Controllers\Web;

use App\Consts\Util;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\AssignmentService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    use Utils;

    protected $assignmentService;

    public function __construct(AssignmentService $assignmentService)
    {
        $this->assignmentService = $assignmentService;
    }

    public function assignmentsGet(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $userId = $request->session()->get('userid');
        $profile = $request->session()->get('userprofile');

        switch ($profile) {
            case "ADMIN":
                $viewData["assignments"] = $this->assignmentService->getAllAssignments();
                break;
            case "ANNONCEUR":
                $viewData["assignments"] = $this->assignmentService->getClientAssignments($userId);
                break;
            case "DIFFUSEUR":
                $viewData["assignments"] = $this->assignmentService->getAgentAssignments($userId);
                break;
        }

        $this->setViewData($request, $viewData);

        return view('admin.assignments', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin',
            'pagetilte' => 'Affectations',
            'pagecardtilte' => 'Liste des affectations',
        ]);
    }

    public function assignmentGet(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $alert["type"] = "danger";
            $alert["message"] = "Affectation non trouvée";
            return redirect()->route('admin.assignments')->with($alert);
        }

        $viewData["assignment"] = $assignment;

        $this->setViewData($request, $viewData);

        return view('admin.assignments', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin',
            'pagetilte' => 'Détail de l\'affectation',
            'pagecardtilte' => '',
        ]);
    }

    public function assignmentPost(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $request->validate([
            'status' => 'required',
            'vues' => 'required|numeric|min:0',
        ]);

        $assignmentData = [
            'status' => $request->status,
            'vues' => $request->vues,
        ];

        $result = $this->assignmentService->updateAssignment($id, $assignmentData);

        if ($result['success']) {
            $alert["type"] = "success";
            $alert["message"] = "Affectation mise à jour avec succès";
            return redirect()->route('admin.assignments')->with($alert);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            return back()->with($alert);
        }
    }

    public function submitResult(Request $request, $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $request->validate([
            'vues' => 'required|numeric|min:1',
            'files' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi|max:20480',
        ]);

        $result = $this->assignmentService->submitResult($id, [
            'vues' => $request->vues,
            'files' => $request->files,
            'agent_id' => $request->session()->get('userid')
        ]);

        if ($result['success']) {
            $alert["type"] = "success";
            $alert["message"] = "Résultat soumis avec succès";
            return redirect()->route('admin.assignments')->with($alert);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            return back()->with($alert);
        }
    }

    public function showResult(Request $request, string $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $assignment = $this->assignmentService->getAssignmentById($id);

        if (!$assignment) {
            $alert["type"] = "danger";
            $alert["message"] = "Affectation non trouvée";
            return redirect()->route('admin.assignments')->with($alert);
        }

        $viewData["assignment"] = $assignment;

        $this->setViewData($request, $viewData);

        return view('admin.submissions.show', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin',
            'pagetilte' => 'Détail du résultat',
            'pagecardtilte' => '',
        ]);
    }

    public function validateResult(Request $request, string $id)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        if (!$this->isConnected()) {
            return redirect(config('app.url') . '/admin/login')->with($alert);
        }

        $assignment = $this->assignmentService->getAssignmentById($id);

        $result = $this->assignmentService->validate($assignment, $request);

        $alert["type"] = "success";
        $alert["message"] = "Résultat soumis avec succès";
        return back()->with($result);
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
