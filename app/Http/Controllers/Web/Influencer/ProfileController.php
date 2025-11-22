<?php
// File: app/Http/Controllers/Web/Influencer/ProfileController.php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Services\CategoryService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    use Utils;

    protected $userService;
    protected $categoryService;

    public function __construct(
        UserService $userService,
        CategoryService $categoryService
    ) {
        $this->userService = $userService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);

        $userId = $request->session()->get('userid');

        // Récupérer l'objet utilisateur complet avec toutes les relations
        $user = $this->userService->getUserById($userId);

        // Si l'utilisateur est trouvé
        if ($user) {
            // Stocker l'objet utilisateur complet
            $viewData["userObject"] = $user;

            // Récupérer la liste des pays directement de la base de données
            $viewData["countries"] = DB::table('countries')
                ->select('id', 'name')
                ->where('enabled', true)
                ->orderBy('name')
                ->get();

            // Récupérer la liste des localités directement de la base de données
            // Correction: utiliser "active" au lieu de "enabled" pour les localités
            $viewData["localities"] = DB::table('localities')
                ->select('id', 'name')
                ->where('type', 2)
                ->orderBy('name')
                ->get();
        } else {
            // Si l'utilisateur n'est pas trouvé, rediriger vers la page de connexion
            return redirect()->route('admin.login')
                ->with('type', 'danger')
                ->with('message', 'Utilisateur non trouvé. Veuillez vous reconnecter.');
        }

        $viewData["categories"] = $this->categoryService->getAllCategories();
        $viewData["userCategories"] = $this->userService->getUserCategories($userId);
        $viewData["assignmentStats"] = $this->userService->getAssignmentStats($userId);

        $this->setViewData($request, $viewData);

        return view('influencer.profile.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Mon Profil',
            'pagetilte' => 'Mon Profil',
            'pagecardtilte' => 'Informations personnelles'
        ]);
    }

    public function update(Request $request)
    {
        $userId = $request->session()->get('userid');

        // Valider les données soumises
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'country_id' => 'nullable|string',
            'locality_id' => 'nullable|string',
            'vuesmoyen' => 'required|numeric|min:0',
            'categories' => 'nullable|array',
            'phone' => 'nullable|string',
            'instagram' => 'nullable|string',
            'tiktok' => 'nullable|string',
            'youtube' => 'nullable|string',
        ]);

        $userData = [
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'country_id' => $request->input('country_id'),
            'locality_id' => $request->input('locality_id'),
            'vuesmoyen' => $request->input('vuesmoyen'),
            'phone' => $request->input('phone'),
            'categories' => $request->input('categories'),
            'instagram' => $request->input('instagram'),
            'tiktok' => $request->input('tiktok'),
            'youtube' => $request->input('youtube'),
        ];

        $result = $this->userService->updateUser($userId, $userData);

        if ($result['success']) {
            return redirect()->back()
                ->with('type', 'success')
                ->with('message', 'Profil mis à jour avec succès');
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
