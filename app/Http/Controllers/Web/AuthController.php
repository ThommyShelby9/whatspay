<?php

namespace App\Http\Controllers\Web;

use App\Consts\Util;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contenttype;
use App\Models\Country;
use App\Models\Lang;
use App\Models\Occupation;
use App\Models\Role;
use App\Models\Study;
use App\Models\User;
use App\Services\AuthService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL; // Ajout de l'import URL
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use Utils;
    
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function loginGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $viewData['typeroles'] = [];
        foreach (Util::TYPES_ROLE as $key => $value) {
            array_push($viewData['typeroles'], $value);
        }

        $this->setViewData($request, $viewData);
        return view('auth.login', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Connexion', 
            'pagecardtilte' => '',
        ]);
    }

    public function loginPost(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $request->validate([
            'profil' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $result = $this->authService->login(
            $request->email, 
            $request->password, 
            $request->has('rememberMe'),
            $request->profil
        );

        if ($result['success']) {
            // Si login réussi, redirige vers dashboard
            return $this->redirect($request, $alert);
        } else {
            // Si échec, affiche message d'erreur
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            $url = URL::route('admin.login', [], true, config('app.url'));
            return redirect()->to($url)->with($alert);
        }
    }

    public function registrationGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $requestData = $request->all();
        $viewData['typeroles'] = [];
        foreach (Util::TYPES_ROLE as $key => $value) {
            if ($value !== Util::TYPES_ROLE["ADMIN"]) {
                array_push($viewData['typeroles'], $value);
            }
        }

        $localities = DB::select("select * from localities where type = 2 order by localities.name asc");
        $countries = Country::all();
        $viewData["countries"] = $countries;
        $viewData["countriesJson"] = json_encode($countries);

        $viewData["bjId"] = '';
        foreach ($viewData["countries"] as $country) {
            if (strtoupper($country->iso2) == "BJ") {
                $viewData["bjId"] = $country->id;
            }
        }

        $viewData["profil"] = (!empty($requestData["profil"]) ? strtoupper($requestData["profil"]) : '');
        $viewData["occupations"] = Occupation::all();
        $viewData["localities"] = $localities;
        $viewData["localitiesJson"] = json_encode($localities);
        $c = Category::all();
        $viewData["categories"] = $c;
        $viewData["categoriesJson"] = json_encode($c);
        $viewData["langs"] = Lang::all();
        $ct = Contenttype::all();
        $viewData["contenttypes"] = $ct;
        $viewData["contenttypesJson"] = json_encode($ct);
        $s = Study::all();
        $viewData["studies"] = $s;
        $viewData["studiesJson"] = json_encode($s);
        
        $this->setViewData($request, $viewData);
        return view('auth.registration', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Inscription', 
            'pagecardtilte' => '',
        ]);
    }

    public function registrationPost(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        // Règles de validation de base
        $rules = [
            'prenom' => 'required|max:255',
            'nom' => 'required|max:255',
            'country' => 'required',
            'phonecountry' => 'required',
            'phone' => 'required|min:8|max:13',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()],
            'profil' => 'required',
            'termes' => 'accepted',
        ];
        
        // Règles conditionnelles selon le profil
        if ($request->profil === 'DIFFUSEUR') {
            $rules['vuesmoyen'] = 'required|numeric|min:1';
            $rules['lang'] = 'required';
            $rules['study'] = 'required';
            
            // Valider qu'au moins une catégorie est sélectionnée
            $hasCategorySelected = false;
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'c_') === 0 && $value) {
                    $hasCategorySelected = true;
                    break;
                }
            }
            
            if (!$hasCategorySelected) {
                return back()
                    ->withInput()
                    ->withErrors(['categories' => 'Veuillez sélectionner au moins une catégorie']);
            }
            
            // Valider qu'au moins un type de contenu est sélectionné
            $hasContentTypeSelected = false;
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'ct_') === 0 && $value) {
                    $hasContentTypeSelected = true;
                    break;
                }
            }
            
            if (!$hasContentTypeSelected) {
                return back()
                    ->withInput()
                    ->withErrors(['contenttypes' => 'Veuillez sélectionner au moins un type de contenu']);
            }
        } 
         else {
            // Règle explicite pour les autres profils (comme ANNONCEUR)
            $rules['vuesmoyen'] = 'nullable|numeric';
        }
        
        // Valider les données selon les règles définies
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Construction des données utilisateur
        $userData = [
            'lastname' => $request->nom,
            'firstname' => $request->prenom,
            'email' => $request->email,
            'password' => $request->password,
            'country_id' => $request->country,
            'locality_id' => $request->locality,
            'study_id' => $request->study,
            'lang_id' => $request->lang,
            'occupation_id' => $request->occupation,
            'occupation' => $request->autre_occupation,
            'phonecountry_id' => $request->phonecountry,
            'phone' => $request->phone,
            'vuesmoyen' => $request->vuesmoyen,
            'profil' => $request->profil,
        ];

        // Collecte des catégories et types de contenu sélectionnés
        $selectedCategories = [];
        $selectedContentTypes = [];

        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'c_') === 0 && $value) {
                $categoryId = substr($key, 2); // Extraire l'ID de la catégorie
                $selectedCategories[] = $categoryId;
            }
            
            if (strpos($key, 'ct_') === 0 && $value) {
                $contentTypeId = substr($key, 3); // Extraire l'ID du type de contenu
                $selectedContentTypes[] = $contentTypeId;
            }
        }

        $userData['categories'] = $selectedCategories;
        $userData['contentTypes'] = $selectedContentTypes;

        $result = $this->authService->register($userData);

        if ($result['success']) {
            $url = URL::route('admin.login', [], true, config('app.url'));
            return redirect()->to($url)->with([
                'message' => 'Inscription enregistrée avec succès. Un code de vérification a été envoyé à votre email. Veuillez vérifier votre boîte mail pour valider votre compte.',
                'type' => 'success'
            ]);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            
            // En cas d'erreur, on recharge la page d'inscription avec les données
            return back()->withInput()->with($alert);
        }
    }

    public function verifyAccountGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $this->setViewData($request, $viewData);
        return view('auth.verify_account', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Vérification de compte', 
            'pagetilte' => 'Vérification de compte', 
            'pagecardtilte' => '',
        ]);
    }

    public function verifyAccountPost(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $request->validate([
            'email' => 'required|email',
            'verification_code' => 'required|string|size:8'
        ]);

        $result = $this->authService->verifyAccount($request->email, $request->verification_code);

        if ($result['success']) {
            $url = URL::route('admin.login', [], true, config('app.url'));
            return redirect()->to($url)->with([
                'message' => 'Votre compte a été vérifié avec succès.',
                'type' => 'success'
            ]);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            return back()->with($alert);
        }
    }

    public function forgotten_passwordPageGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $this->setViewData($request, $viewData);
        return view('auth.forgotten_password', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Mot de passe oublié', 
            'pagetilte' => 'Mot de passe oublié', 
            'pagecardtilte' => '',
        ]);
    }

    public function forgotten_passwordPagePost(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $request->validate([
            'email' => 'required|email'
        ]);

        $result = $this->authService->sendResetCode($request->email);

        if ($result['success']) {
            $alert["type"] = "info";
            $alert["message"] = "Un code de réinitialisation a été envoyé à votre adresse email.";
            $url = URL::route('auth.reset_password', [], true, config('app.url'));
            return redirect()->to($url)->with($alert);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            return back()->with($alert);
        }
    }

    public function reset_passwordGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $this->setViewData($request, $viewData);
        return view('auth.reset_password', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Réinitialisation de mot de passe', 
            'pagetilte' => 'Réinitialisation de mot de passe', 
            'pagecardtilte' => '',
        ]);
    }

    public function reset_passwordPost(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $request->validate([
            'email' => 'required|email',
            'reset_code' => 'required|string|size:8',
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()],
        ]);

        $result = $this->authService->resetPassword(
            $request->email, 
            $request->reset_code, 
            $request->password
        );

        if ($result['success']) {
            $url = URL::route('auth.login', [], true, config('app.url'));
            return redirect()->to($url)->with([
                'message' => 'Votre mot de passe a été réinitialisé avec succès.',
                'type' => 'success'
            ]);
        } else {
            $alert["type"] = "danger";
            $alert["message"] = $result['message'];
            return back()->with($alert);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request);
        $url = URL::route('admin.login', [], true, config('app.url'));
        return redirect()->to($url)->with([
            'message' => 'Vous avez été déconnecté avec succès.',
            'type' => 'success'
        ]);
    }

    private function isConnected()
    {
        return (Auth::viaRemember() || Auth::check());
    }

    private function redirect(Request &$request, &$alert)
    {
        $profil = ($request->session()->has('userprofile') ? $request->session()->get('userprofile') : "");
        $requestData = $request->all();
        
        switch ($profil) {
            case "ADMIN":
                $url = URL::route('admin.dashboard', [], true, config('app.url'));
                return redirect()->to($url)->with($alert);
                break;
            case "ANNONCEUR":
                $url = URL::route('admin.client.dashboard', [], true, config('app.url'));
                return redirect()->to($url)->with($alert);
                break;
            case "DIFFUSEUR":
                $url = URL::route('admin.influencer.dashboard', [], true, config('app.url'));
                return redirect()->to($url)->with($alert);
                break;
            default:
                if (!empty($requestData["draft"])) {
                    $url = URL::route('admin.dashboard', [], true, config('app.url'));
                    return redirect()->to($url)->with($alert);
                } else {
                    $url = URL::route('comingsoon', [], true, config('app.url'));
                    return redirect()->to($url)->with($alert);
                }
                break;
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
        $viewData['uri'] = Route::currentRouteName();
        $viewData['baseUrl'] = config('app.url');
        $viewData['version'] = gmdate('YmdHis');
        $viewData['user'] = ($request->session()->has('user') ? $request->session()->get('user') : "");
        $viewData['userid'] = ($request->session()->has('userid') ? $request->session()->get('userid') : "");
        $viewData['userprofile'] = ($request->session()->has('userprofile') ? $request->session()->get('userprofile') : "");
        $viewData['userrights'] = ($request->session()->has('userrights') ? (json_decode($request->session()->get('userrights'), true)) : []);
        $viewData['userfirstname'] = ($request->session()->has('userfirstname') ? $request->session()->get('userfirstname') : "");
        $viewData['userlastname'] = ($request->session()->has('userlastname') ? $request->session()->get('userlastname') : "");
    }

    // Méthodes pour la page de sélection de profil et les formulaires spécifiques

    public function registrationHomeGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        $this->setViewData($request, $viewData);
        return view('auth.registration', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Inscription', 
            'pagetilte' => 'Choisir un profil', 
            'pagecardtilte' => '',
        ]);
    }

    public function registrationDiffuseurGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        // Récupérer les données nécessaires pour le formulaire DIFFUSEUR
        $localities = DB::select("select * from localities where type = 2 order by localities.name asc");
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
        $viewData["localities"] = $localities;
        $viewData["localitiesJson"] = json_encode($localities);
        $c = Category::all();
        $viewData["categories"] = $c;
        $viewData["categoriesJson"] = json_encode($c);
        $viewData["langs"] = Lang::all();
        $ct = Contenttype::all();
        $viewData["contenttypes"] = $ct;
        $viewData["contenttypesJson"] = json_encode($ct);
        $s = Study::all();
        $viewData["studies"] = $s;
        $viewData["studiesJson"] = json_encode($s);
        
        $this->setViewData($request, $viewData);
        return view('auth.register_diffuseur', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Inscription Diffuseur', 
            'pagetilte' => 'Inscription Diffuseur', 
            'pagecardtilte' => '',
        ]);
    }

    public function registrationAnnonceurGet(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if ($this->isConnected()) {
            return $this->redirect($request, $alert);
        }

        // Récupérer les données nécessaires pour le formulaire ANNONCEUR
        $localities = DB::select("select * from localities where type = 2 order by localities.name asc");
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
        $viewData["localities"] = $localities;
        $viewData["localitiesJson"] = json_encode($localities);
        
        $this->setViewData($request, $viewData);
        return view('auth.register_annonceur', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Inscription Annonceur', 
            'pagetilte' => 'Inscription Annonceur', 
            'pagecardtilte' => '',
        ]);
    }

    public function registrationDiffuseurPost(Request $request)
    {
        // Ajoutez automatiquement le type de profil
        $request->merge(['profil' => 'DIFFUSEUR']);
        
        // Appeler la méthode d'inscription générale
        return $this->registrationPost($request);
    }

    public function registrationAnnonceurPost(Request $request)
    {
        // Ajoutez automatiquement le type de profil
        $request->merge(['profil' => 'ANNONCEUR']);
        
        // Appeler la méthode d'inscription générale
        return $this->registrationPost($request);
    }
}