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
use App\Models\Role;
use App\Models\Study;
use App\Models\Task;
use App\Services\UserService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;

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

        // Vérifier si une action et un ID sont fournis dans la requête
        $action = $request->query('action');
        $id = $request->query('id');
        
        // Si action et ID sont fournis, traiter l'action spécifique
        if ($action && $id) {
            return $this->handleUserAction($request, $action, $id, $group);
        }

        if (!in_array($group, ["admin", "annonceur", "diffuseur", "all"])) {
            $alert["type"] = "danger";
            $alert["message"] = "Le lien recherché n'est pas valide";
            return redirect(config('app.url').'/admin/dashboard')->with($alert);
        }

        $pagetilte = "";
        $pagecardtilte = "";
        $requestData = $request->all();

        // Initialiser les filtres
        foreach (["filtre_country", "filtre_locality", "filtre_profile", "filtre_status"] as $item) {
            $viewData[$item] = $request->input($item, "all");
        }
        foreach (["filtre_occupation", "filtre_study", "filtre_category", "filtre_contenu", "filtre_lang"] as $item) {
            $viewData[$item] = $request->input($item, []);
        }

        // Récupérer les utilisateurs selon le profil
        switch ($group) {
            case "admin":
                $pagetilte = "Admins";
                $pagecardtilte = "Liste des Admins";
                $viewData["items"] = $this->userService->getUsersByProfile(Util::TYPES_ROLE["ADMIN"], $this->getFiltersFromRequest($request));
                break;
            case "annonceur":
                $pagetilte = "Annonceurs";
                $pagecardtilte = "Liste des Annonceurs";
                $viewData["items"] = $this->userService->getUsersByProfile(Util::TYPES_ROLE["ANNONCEUR"], $this->getFiltersFromRequest($request));
                break;
            case "diffuseur":
                $pagetilte = "Diffuseurs";
                $pagecardtilte = "Liste des Diffuseurs";
                $viewData["items"] = $this->userService->getUsersByProfile(Util::TYPES_ROLE["DIFFUSEUR"], $this->getFiltersFromRequest($request));
                break;
            case "all":
                $pagetilte = "Tous les utilisateurs";
                $pagecardtilte = "Liste complète";
                $viewData["items"] = $this->userService->getUsers($this->getFiltersFromRequest($request));
                break;
        }

        // Calculer le total des vues moyennes
        $viewData["vuesmoyen"] = 0;
        foreach ($viewData["items"] as $item) {
            $viewData["vuesmoyen"] += isset($item->vuesmoyen) ? $item->vuesmoyen : 0;
        }

        // Récupérer les données pour les filtres
        $countries = Country::all();
        $viewData["countries"] = $countries;
        $viewData["countriesJson"] = json_encode($countries);

        $viewData["bjId"] = '';
        foreach ($viewData["countries"] as $country) {
            if (strtoupper($country->code) == "BJ") {
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

        $this->setViewData($request, $viewData);
        
        // Sélectionner le template selon le groupe
        $template = 'admin.' . ($group != 'all' ? $group : 'users');
        
        return view($template, [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => $pagetilte, 
            'pagecardtilte' => $pagecardtilte,
        ]);
    }
    
    /**
     * Traite les actions spécifiques sur un utilisateur
     */
    private function handleUserAction(Request $request, $action, $id, $group)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        // Initialiser les filtres avec des valeurs par défaut
        $viewData["filtre_country"] = "all";
        $viewData["filtre_locality"] = "all";
        $viewData["filtre_profile"] = "all";
        $viewData["filtre_status"] = "all";
        $viewData["filtre_occupation"] = [];
        $viewData["filtre_study"] = [];
        $viewData["filtre_category"] = [];
        $viewData["filtre_contenu"] = [];
        $viewData["filtre_lang"] = [];
        
        // Récupérer l'utilisateur
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            $alert['type'] = 'danger';
            $alert['message'] = 'Utilisateur non trouvé';
            return redirect()->route('admin.users', ['group' => $group])->with($alert);
        }
        
        // Récupérer le profil de l'utilisateur
        $userRoles = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('role_user.user_id', $id)
            ->pluck('roles.typerole')
            ->toArray();
            
        $viewData['userDetails'] = $user;
        $viewData['userRoles'] = $userRoles;
        
        // Récupérer les données communes pour les formulaires
        $viewData["countries"] = Country::all();
        $viewData["localities"] = Locality::where('type', 2)->orderBy('name', 'asc')->get();
        $viewData["langs"] = Lang::all();
        $viewData["studies"] = Study::all();
        $viewData["occupations"] = Occupation::all();
        $viewData["categories"] = Category::all();
        $viewData["contenttypes"] = Contenttype::all();
        
        $this->setViewData($request, $viewData);
        
        $pagetitle = '';
        $pagecardtitle = '';
        
        switch ($action) {
            case 'view':
                $pagetitle = 'Détails utilisateur';
                $pagecardtitle = $user->firstname . ' ' . $user->lastname;
                break;
                
            case 'edit':
                $pagetitle = 'Modifier utilisateur';
                $pagecardtitle = 'Édition de ' . $user->firstname . ' ' . $user->lastname;
                break;
                
            case 'stats':
                // Vérifier si l'utilisateur est un diffuseur
                if (!in_array('DIFFUSEUR', $userRoles)) {
                    $alert['type'] = 'warning';
                    $alert['message'] = 'Les statistiques ne sont disponibles que pour les diffuseurs';
                    return redirect()->route('admin.users', ['group' => $group])->with($alert);
                }
                
                $viewData['stats'] = $this->getDiffuseurStats($id);
                $pagetitle = 'Statistiques diffuseur';
                $pagecardtitle = 'Performances de ' . $user->firstname . ' ' . $user->lastname;
                break;
                
            case 'campaigns':
                // Vérifier si l'utilisateur est un annonceur
                if (!in_array('ANNONCEUR', $userRoles)) {
                    $alert['type'] = 'warning';
                    $alert['message'] = 'Les campagnes ne sont disponibles que pour les annonceurs';
                    return redirect()->route('admin.users', ['group' => $group])->with($alert);
                }
                
                // Récupérer les campagnes de l'annonceur
                $viewData['campaigns'] = Task::where('client_id', $id)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                    
                $pagetitle = 'Campagnes annonceur';
                $pagecardtitle = 'Campagnes de ' . $user->firstname . ' ' . $user->lastname;
                break;
                
            default:
                $alert['type'] = 'warning';
                $alert['message'] = 'Action non reconnue';
                return redirect()->route('admin.users', ['group' => $group])->with($alert);
        }
        
        // Retourner la vue appropriée
        $template = 'admin.' . ($group != 'all' ? $group : 'users');
        
        return view($template, [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | ' . $pagetitle, 
            'pagetilte' => $pagetitle, 
            'pagecardtilte' => $pagecardtitle,
        ]);
    }
    
    /**
     * Traite les actions sur les utilisateurs (POST)
     */
    public function usersPost(Request $request, $group)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        // Traiter les requêtes AJAX
        if ($request->ajax()) {
            $action = $request->input('action');
            
            if ($action === 'toggle_status') {
                return $this->toggleUserStatus($request);
            }
            
            return Response::json(['success' => false, 'message' => 'Action non reconnue']);
        }

        // Traiter les actions de formulaire
        $action = $request->input('action');
        
        switch ($action) {
            case 'delete_user':
                return $this->deleteUser($request, $group);
                
            case 'update_user':
                return $this->updateUser($request, $group);
                
            default:
                $alert['type'] = 'warning';
                $alert['message'] = 'Action non reconnue';
                return redirect()->route('admin.users', ['group' => $group])->with($alert);
        }
    }
    
    /**
     * Active/désactive un utilisateur (AJAX)
     */
    private function toggleUserStatus(Request $request)
    {
        $userId = $request->input('user_id');
        $enabled = $request->input('enabled');
        
        try {
            $result = $this->userService->updateUser($userId, ['enabled' => $enabled]);
            
            if ($result['success']) {
                return Response::json([
                    'success' => true,
                    'message' => 'Le statut a été modifié avec succès'
                ]);
            }
            
            return Response::json([
                'success' => false,
                'message' => $result['message']
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Supprime un utilisateur
     */
/**
 * Supprime un utilisateur
 */
/**
 * Supprime un utilisateur
 */
/**
 * Supprime un utilisateur
 */
private function deleteUser(Request $request, $group)
{
    $userId = $request->input('user_id');
    
    // Vérification que l'ID est bien présent
    if (empty($userId)) {
        $alert['type'] = 'danger';
        $alert['message'] = 'ID utilisateur manquant';
        return redirect()->route('admin.users', ['group' => $group])->with($alert);
    }
    
    try {
        // Vérifier si l'utilisateur existe
        $userExists = DB::table('users')->where('id', $userId)->exists();
        
        if (!$userExists) {
            $alert['type'] = 'danger';
            $alert['message'] = 'Utilisateur non trouvé (ID: ' . $userId . ')';
            return redirect()->route('admin.users', ['group' => $group])->with($alert);
        }
        
        // Supprimer les relations associées
        DB::table('role_user')->where('user_id', $userId)->delete();
        DB::table('category_user')->where('user_id', $userId)->delete();
        DB::table('contenttype_user')->where('user_id', $userId)->delete();
        
        // Supprimer l'utilisateur
        $deleted = DB::table('users')->where('id', $userId)->delete();
        
        if ($deleted) {
            $alert['type'] = 'success';
            $alert['message'] = 'Utilisateur supprimé avec succès';
        } else {
            $alert['type'] = 'warning';
            $alert['message'] = 'Aucun utilisateur n\'a été supprimé';
        }
    } catch (\Exception $e) {
        $alert['type'] = 'danger';
        $alert['message'] = 'Erreur lors de la suppression: ' . $e->getMessage();
    }
    
    return redirect()->route('admin.users', ['group' => $group])->with($alert);
}
    
    /**
     * Met à jour un utilisateur
     */
    private function updateUser(Request $request, $group)
    {
        $userId = $request->input('user_id');
        
        // Valider les données
        $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed'
        ]);
        
        try {
            $userData = [
                'firstname' => $request->input('firstname'),
                'lastname' => $request->input('lastname'),
                'phone' => $request->input('phone'),
                'country_id' => $request->input('country_id'),
                'locality_id' => $request->input('locality_id'),
                'enabled' => $request->has('enabled') ? 1 : 0
            ];
            
            // Ajouter les champs spécifiques au diffuseur si présents
            if ($request->has('vuesmoyen')) {
                $userData['vuesmoyen'] = $request->input('vuesmoyen');
            }
            
            if ($request->has('lang_id')) {
                $userData['lang_id'] = $request->input('lang_id');
            }
            
            if ($request->has('study_id')) {
                $userData['study_id'] = $request->input('study_id');
            }
            
            // Mettre à jour le mot de passe si fourni
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->input('password'));
            }
            
            $result = $this->userService->updateUser($userId, $userData);
            
            if ($result['success']) {
                $alert['type'] = 'success';
                $alert['message'] = 'Utilisateur mis à jour avec succès';
            } else {
                $alert['type'] = 'danger';
                $alert['message'] = $result['message'];
            }
        } catch (\Exception $e) {
            $alert['type'] = 'danger';
            $alert['message'] = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        }
        
        return redirect()->route('admin.users', ['group' => $group, 'action' => 'view', 'id' => $userId])->with($alert);
    }
    
    /**
     * Récupère les statistiques d'un diffuseur
     */
    private function getDiffuseurStats($userId)
    {
        // Total des campagnes
        $totalCampaigns = DB::table('assignments')
            ->where('influencer_id', $userId)
            ->count();
            
        // Campagnes terminées
        $completedCampaigns = DB::table('assignments')
            ->where('influencer_id', $userId)
            ->where('status', 'COMPLETED')
            ->count();
            
        // Campagnes en cours
        $activeCampaigns = DB::table('assignments')
            ->where('influencer_id', $userId)
            ->where('status', 'IN_PROGRESS')
            ->count();
            
        // Revenus totaux
        $totalEarnings = DB::table('assignments')
            ->where('influencer_id', $userId)
            ->where('status', 'COMPLETED')
            ->sum('gain');
            
        // Statistiques mensuelles
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $startOfMonth = $month->copy()->startOfMonth();
            $endOfMonth = $month->copy()->endOfMonth();
            
            $monthlyCompletions = DB::table('assignments')
                ->where('influencer_id', $userId)
                ->where('status', 'COMPLETED')
                ->whereBetween('submission_date', [$startOfMonth, $endOfMonth])
                ->count();
                
            $monthlyEarnings = DB::table('assignments')
                ->where('influencer_id', $userId)
                ->where('status', 'COMPLETED')
                ->whereBetween('submission_date', [$startOfMonth, $endOfMonth])
                ->sum('gain');
                
            $monthlyStats[] = [
                'month' => $month->format('M Y'),
                'completions' => $monthlyCompletions,
                'earnings' => $monthlyEarnings
            ];
        }
        
        return [
            'totalCampaigns' => $totalCampaigns,
            'completedCampaigns' => $completedCampaigns,
            'activeCampaigns' => $activeCampaigns,
            'totalEarnings' => $totalEarnings,
            'monthlyStats' => $monthlyStats
        ];
    }
    
    /**
     * Convertit les paramètres de requête en filtres pour le service utilisateur
     */
    private function getFiltersFromRequest(Request $request)
    {
        return [
            'country_id' => $request->input('filtre_country') !== 'all' ? $request->input('filtre_country') : null,
            'locality_id' => $request->input('filtre_locality') !== 'all' ? $request->input('filtre_locality') : null,
            'profile' => $request->input('filtre_profile') !== 'all' ? $request->input('filtre_profile') : null,
            'enabled' => $request->input('filtre_status') !== 'all' ? $request->input('filtre_status') : null,
            'occupation_ids' => $request->input('filtre_occupation', []),
            'study_ids' => $request->input('filtre_study', []),
            'category_ids' => $request->input('filtre_category', []),
            'contenttype_ids' => $request->input('filtre_contenu', []),
            'lang_ids' => $request->input('filtre_lang', []),
        ];
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