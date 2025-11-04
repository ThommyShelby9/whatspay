<?php

namespace App\Http\Controllers\Web;

use App\Consts\Util;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contenttype;
use App\Models\Country;
use App\Models\Lang;
use App\Models\Locality;
use App\Models\Occupation;
use App\Models\Study;
use App\Services\UserService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    use Utils;
    
    protected $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function usersGet(Request $request, $group)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        if (!in_array($group, ["admin", "annonceur", "diffuseur"])) {
            $alert["type"] = "danger";
            $alert["message"] = "Le lien recherché n'est pas valide";
            return redirect(config('app.url').'/admin/dashboard')->with($alert);
        }

        $pagetilte = "";
        $pagecardtilte = "";
        $requestData = $request->all();

        // Initialiser les filtres
        foreach (["filtre_country", "filtre_locality"] as $item) {
            $viewData[$item] = "";
        }
        foreach (["filtre_occupation", "filtre_study", "filtre_category", "filtre_contenu", "filtre_lang"] as $item) {
            $viewData[$item] = [];
        }

        // Récupérer les filtres
        $filters = [
            'country_id' => !empty($requestData["filtre_country"]) ? $requestData["filtre_country"] : null,
            'locality_id' => !empty($requestData["filtre_locality"]) ? $requestData["filtre_locality"] : null,
            'occupation_ids' => !empty($requestData["filtre_occupation"]) ? $requestData["filtre_occupation"] : [],
            'study_ids' => !empty($requestData["filtre_study"]) ? $requestData["filtre_study"] : [],
            'category_ids' => !empty($requestData["filtre_category"]) ? $requestData["filtre_category"] : [],
            'contenttype_ids' => !empty($requestData["filtre_contenu"]) ? $requestData["filtre_contenu"] : [],
            'lang_ids' => !empty($requestData["filtre_lang"]) ? $requestData["filtre_lang"] : [],
        ];

        // Récupérer les utilisateurs selon le profil
        switch ($group) {
            case "admin":
                $pagetilte = "Admins";
                $pagecardtilte = "Liste des Admins";
                $viewData["items"] = $this->userService->getUsersByProfile(Util::TYPES_ROLE["ADMIN"], $filters);
                break;
            case "annonceur":
                $pagetilte = "Annonceurs";
                $pagecardtilte = "Liste des Annonceurs";
                $viewData["items"] = $this->userService->getUsersByProfile(Util::TYPES_ROLE["ANNONCEUR"], $filters);
                break;
            case "diffuseur":
                $pagetilte = "Diffuseurs";
                $pagecardtilte = "Liste des Diffuseurs";
                $viewData["items"] = $this->userService->getUsersByProfile(Util::TYPES_ROLE["DIFFUSEUR"], $filters);
                break;
        }

        // Calculer le total des vues moyennes
        $viewData["vuesmoyen"] = 0;
        foreach ($viewData["items"] as $item) {
            $viewData["vuesmoyen"] += $item->vuesmoyen;
        }

        // Récupérer les données pour les filtres
        $countries = Country::all();
        $viewData["countries"] = $countries;
        $viewData["countriesJson"] = json_encode($countries);

        $viewData["bjId"] = '';
        foreach ($viewData["countries"] as $country) {
            if (strtoupper($country->iso2) == "BJ") {
                $viewData["bjId"] = $country->id;
            }
        }

        $viewData["occupations"] = Occupation::all();

        $localities = Locality::where('type', 2)->orderBy('name', 'asc')->get();
        $viewData["localities"] = $localities;
        $viewData["localitiesJson"] = json_encode($localities);

        $viewData["categories"] = Category::all();
        $viewData["categoriesJson"] = json_encode($viewData["categories"]);

        $viewData["langs"] = Lang::all();

        $viewData["contenttypes"] = Contenttype::all();
        $viewData["contenttypesJson"] = json_encode($viewData["contenttypes"]);

        $viewData["studies"] = Study::all();
        $viewData["studiesJson"] = json_encode($viewData["studies"]);

        // Ajouter tous les filtres aux données de vue
        foreach ($requestData as $key => $value) {
            $viewData[$key] = $value;
        }

        $this->setViewData($request, $viewData);
        return view('admin.' . $group, [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => $pagetilte, 
            'pagecardtilte' => $pagecardtilte,
        ]);
    }
    
    public function usersPost(Request $request, $group)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        // Traitement des actions sur les utilisateurs
        // Par exemple: activation/désactivation, suppression, etc.
        
        return redirect()->route('admin.users', ['group' => $group])->with([
            'message' => 'Opération effectuée avec succès',
            'type' => 'success'
        ]);
    }
    
    public function profile(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $userId = $request->session()->get('userid');
        $user = $this->userService->getUserById($userId);

        if (!$user) {
            $alert["type"] = "danger";
            $alert["message"] = "Utilisateur non trouvé";
            return redirect(config('app.url').'/admin/dashboard')->with($alert);
        }

        $viewData["user"] = $user;
        $viewData["countries"] = Country::all();
        $viewData["localities"] = Locality::where('type', 2)->orderBy('name', 'asc')->get();
        $viewData["langs"] = Lang::all();
        $viewData["studies"] = Study::all();
        $viewData["occupations"] = Occupation::all();
        
        $this->setViewData($request, $viewData);
        
        return view('admin.profile', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Mon profil', 
            'pagecardtilte' => 'Informations personnelles',
        ]);
    }
    
    public function updateProfile(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $request->validate([
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'country_id' => 'required',
            'phone' => 'required|min:8|max:13',
        ]);

        $userId = $request->session()->get('userid');
        
        $userData = [
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'country_id' => $request->country_id,
            'locality_id' => $request->locality_id,
            'lang_id' => $request->lang_id,
            'study_id' => $request->study_id,
            'occupation_id' => $request->occupation_id,
            'occupation' => $request->occupation,
            'phone' => $request->phone,
            'vuesmoyen' => $request->vuesmoyen,
        ];

        $result = $this->userService->updateUser($userId, $userData);

        if ($result['success']) {
            $alert["type"] = "success";
            $alert["message"] = "Profil mis à jour avec succès";
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
        }

        return redirect()->route('admin.profile')->with($alert);
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