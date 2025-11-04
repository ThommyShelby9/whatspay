<?php

namespace App\Http\Controllers;

use App\Consts\Util;
use App\Mail\Email;
use App\Models\Category;
use App\Models\Contenttype;
use App\Models\Country;
use App\Models\Lang;
use App\Models\Occupation;
use App\Models\Phone;
use App\Models\Role;
use App\Models\Study;
use App\Models\Task;
use App\Models\User;
use App\Traits\Utils;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use Illuminate\Validation\Rules\Password;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use WasenderApi\WasenderClient;


class FrontController extends Controller
{

    use Utils;

    function redirect (Request &$request, &$alert) {
      $profil = ( $request->session()->has('userprofile') ? $request->session()->get('userprofile') : "" );
      $requestData = $request->all();
      switch ($profil){
        case "ADMIN":
          return redirect(config('app.url').'/admin/dashboard')->with($alert);
          break;
        default:
          if(!empty($requestData["draft"])){
            return redirect(config('app.url').'/admin/dashboard')->with($alert);
          }else{
            return redirect(config('app.url').'/comingsoon')->with($alert);
          }
          break;
      }
    }

    public function __construct(){}

    public function baseFunction(Request $request, $message){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        $requestData = $request->all();
        $countries = Country::all();

        $this->setViewData($request, $viewData);
        return view('demo.jedonnepourrow_callback', [
            'baseurl' => config('app.url'),
            'version' => gmdate("YmdHis"),
            'countries' => $countries, 'alert' => $alert, 'viewData' => $viewData,
            'title' => 'WhatsPAY',
        ]);
    }

    public function home(Request $request){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      $requestData = $request->all();
      $countries = Country::all();

      $this->setViewData($request, $viewData);
      return view('home', [
        'baseurl' => config('app.url'),
        'version' => gmdate("YmdHis"),
        'countries' => $countries, 'alert' => $alert, 'viewData' => $viewData,
        'title' => 'WhatsPAY',
      ]);
    }

    public function page(Request $request, $page){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      $requestData = $request->all();
      $countries = Country::all();

      $titlepage = "";

      switch ($page) {
        case 'contact':
          $titlepage = "CONTACTEZ NOUS";
          break;
        case 'support':
          $titlepage = "SUPPORT";
          break;
        case 'faq':
          $titlepage = "FOIRE AUX QUESTIONS";
          break;
        case 'politique':
          $titlepage = "POLITIQUE DE CONFIDENTIALITE";
          break;
        case 'conditions':
          $titlepage = "CONDITIONS D'UTILISATION";
          break;
        case 'mentions':
          $titlepage = "MENTIONS LEGALES";
          break;
      }

      $this->setViewData($request, $viewData);
      return view(strtolower(__FUNCTION__), [
        'baseurl' => config('app.url'),
        'version' => gmdate("YmdHis"),
        'countries' => $countries, 'alert' => $alert, 'viewData' => $viewData,
        'title' => 'WhatsPAY > '.strtoupper($page),
        'titlepage' => $titlepage,
        'page' => $page
      ]);
    }

    public function sendmessage2(WasenderClient $client, ?string $recipient,$message="") {
      $result = $client->sendText(
        $recipient,
        (!empty($message) ? $message : "Envoi de message ".gmdate("Y-m-d H:i:s"))
      );
      dd($result);
    }

    public function sendmessage(Request $request, $recipient){
      $viewData = [];
      $requestData = $request->all();

      $client = new Client();
      $apiKey = '23018ff7303d259c78f24eeeeb188e0ba3e2278e259831f88dbe679093ee5d6b';
      $url = 'https://www.wasenderapi.com/api/send-message';

      try {
        $response = $client->post($url, [
          'headers' => [
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ],
          'json' => [
            'to' => "+".$recipient,
            'text' => 'Hello, here is your update.'
          ]
        ]);

        echo $response->getBody();
      } catch (RequestException $e) {
        echo "Request failed: " . $e->getMessage();
        if ($e->hasResponse()) {
          echo "\nResponse: " . $e->getResponse()->getBody();
        }
      }

      return json_encode($requestData);
    }

    public function comingsoon(Request $request){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( !$this->isConnected() ) {
        return redirect(config('app.url').'/admin/login')->with($alert);
      }

      $requestData = $request->all();
      $countries = Country::all();

      $this->setViewData($request, $viewData);
      return view(strtolower(__FUNCTION__), [
        'baseurl' => config('app.url'),
        'version' => gmdate("YmdHis"),
        'countries' => $countries, 'alert' => $alert, 'viewData' => $viewData,
        'title' => 'WhatsPAY > Coming Soon'
      ]);
    }

    public function logout(Request $request, $alert = []) {
      // https://laravel.com/docs/11.x/session
      $this->flushSession($request);
      return redirect(config('app.url').'/admin/login')->with($alert);
    }

    private function isConnected (){
        return (Auth::viaRemember() || Auth::check());
    }
 
    private function setAlert(Request &$request, &$alert){
        $alert = [
            'message' => (!empty($request->message) ? $request->message : ( !empty(session('message')) ? session('message') : "" )),
            'type' => (!empty($request->type) ? $request->type : ( !empty(session('type')) ? session('type') : "success" )), //danger warning info
        ];
    }

    public function lastConnection($id){
        User::where('id', $id)->update([
            'lastconnection' => gmdate('Y-m-d H:i:s')
        ]);
    }

    public function flushSession(Request $request)
    {
      if ($request->session()->has('user')) {
        $request->session()->forget('user');
        $request->session()->forget('userid');
        $request->session()->forget('userfirstname');
        $request->session()->forget('userlastname');
        $request->session()->forget('userprofile');
        $request->session()->forget('userrights');
        $request->session()->forget('user');
      }
      Auth::logout();
      $request->session()->flush();
      $request->session()->invalidate();
      $request->session()->regenerateToken();
    }

    public function setViewData(Request &$request, &$viewData){
        $viewData['uri'] = Route::currentRouteName();
        $viewData['baseUrl'] = config('app.url');
        $viewData['version'] = gmdate('YmdHis');
        $viewData['user'] = ( $request->session()->has('user') ? $request->session()->get('user') : "" );
        $viewData['userid'] = ( $request->session()->has('userid') ? $request->session()->get('userid') : "" );
        $viewData['userprofile'] = ( $request->session()->has('userprofile') ? $request->session()->get('userprofile') : "" );
        $viewData['userrights'] = ( $request->session()->has('userrights') ? (json_decode($request->session()->get('userrights'), true)) : [] );
        $viewData['userfirstname'] = ( $request->session()->has('userfirstname') ? $request->session()->get('userfirstname') : "" );
        $viewData['userlastname'] = ( $request->session()->has ('userlastname') ? $request->session()->get('userlastname') : "" );
    }

    public function forgotten_passwordPageGet (Request $request){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( $this->isConnected() ) {
        //return redirect(config('app.url').'/admin/dashboard')->with($alert);
        return $this->redirect ($request, $alert);
      }
  
      $this->setViewData($request, $viewData);
      return view('admin.'.strtolower(__FUNCTION__), [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Mot de passe oublié', 'pagecardtilte' => '',
      ]);
    }

    public function forgotten_passwordPagePost (Request $request){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( $this->isConnected() ) {
        //return redirect(config('app.url').'/admin/dashboard')->with($alert);
        return $this->redirect ($request, $alert);
      }else{

        $request->validate([
          'email' => 'required|email'
        ]);

        $userExist = DB::select("select * from users where email = :email ;", [
          'email' => $request->email
        ]);
        if(count($userExist) == 1){

          if(!empty($userExist[0]->email_verified_at)){
            if(!empty($userExist[0]->enabled)){
              $token = [
                'type' => 'forgotten_password',
                'date' => gmdate("YmdHis"),
                'mail' => $userExist[0]->email,
                'id' => $userExist[0]->id
              ];
              //https://github.com/firebase/php-jwt
              $token = JWT::encode($token, Util::JWTKEY, 'HS256');

              $this->sendDataToQueue([
                'recipient' => $request->email,
                'type' => 'forgotten_password',
                'subject' => 'Récupération de votre mot de passe WhatsPAY',
                'lastname' => $userExist[0]->lastname,
                'firstname' => $userExist[0]->firstname,
                'token' => $token,
                'url' => config('app.url'),
              ], Util::MAILSENDER_QUEUE);

              $alert["type"] = "info";
              $alert["message"] = "Veuillez bien vérifier votre boite mail svp. Un mail vous a été envoyé afin de procéder a la récupération de votre mot de passe.";
              return redirect(config('app.url').'/admin/login')->with($alert);
            }else{
              $alert["type"] = "danger";
              $alert["message"] = "Opération impossible. Compte inactif";
            }
          }else {
            $alert["type"] = "danger";
            $alert["message"] = "Opération impossible. Compte non vérifié";
          }

        } else {
          $alert["type"] = "danger";
          $alert["message"] = "Compte non identifié";
        }

        if (empty($alert["message"])) {
          return redirect(config('app.url').'/admin/login')->with([]);
        }
      }

      $this->setViewData($request, $viewData);
      return view('admin.forgotten_passwordpageget', [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Mot de passe oublié', 'pagecardtilte' => '',
      ]);
    }

    public function mailVerificationPageGet(Request $request, $token)
    {

      $alert = []; $this->setAlert($request, $alert);

      if ( $this->isConnected() ) {
        //return redirect(config('app.url').'/admin/dashboard')->with($alert);
        return $this->redirect ($request, $alert);
      }else{
        if (empty($token)) {
          $alert["type"] = "danger";
          $alert["message"] = "Please provide the verification token";
          return redirect(config('app.url').'/admin/login')->with($alert);
        } else {
          $processingTransaction = false;
          try {
            $decodedToken = JWT::decode($token, new Key(Util::JWTKEY, 'HS256'));
            if(!empty($decodedToken->mail) && !empty($decodedToken->id && !empty($decodedToken->type) && $decodedToken->type == 'registration')){
              $email = $decodedToken->mail;
              $id = $decodedToken->id;
              $userExist = DB::select("select * from users where email = :email and id = :id", [
                'email' => $email,
                'id' => $id
              ]);
              if(count($userExist) == 1){
                if(empty($userExist[0]->email_verified_at)){
                  $user = [
                    'email_verified_at' => gmdate('Y-m-d H:i:s')
                  ];
                  DB::beginTransaction();$processingTransaction = true;
                  $user = User::where('id', $id)->update($user);
                  DB::commit();$processingTransaction = false;

                  return redirect(config('app.url').'/admin/login')->with([
                    'message' => 'Votre compte a été vérifé avec succes.',
                    'type' => 'success' //danger warning info
                  ]);
                }else{
                  $alert["type"] = "danger";
                  $alert["message"] = "Compte déja vérifié.";
                  return redirect(config('app.url').'/admin/login')->with($alert);
                }
              }else{
                $alert["type"] = "danger";
                $alert["message"] = "Compte non identifié.";
                return redirect(config('app.url').'/admin/login')->with($alert);
              }
            }else{
              $alert["type"] = "danger";
              $alert["message"] = "Token invalide";
              return redirect(config('app.url').'/admin/login')->with($alert);
            }
          }catch (Exception $exception){
            if($processingTransaction) {
              DB::rollBack();
            }
            $alert["type"] = "danger";
            $alert["message"] = "Une erreur est survenue durant la vérification";
            return redirect(config('app.url').'/admin/login')->with($alert);
          }
        }
      }

      $alert["type"] = "warning";
      $alert["message"] = "Please provide the verification token";
      return redirect(config('app.url').'/admin/login')->with($alert);

    }

    public function registrationGet (Request $request){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( $this->isConnected() ) {
        //return redirect(config('app.url').'/admin/dashboard')->with($alert);
        return $this->redirect ($request, $alert);
      }

      $requestData = $request->all();
      $viewData['typeroles'] = [];
      foreach (Util::TYPES_ROLE as $key => $value){
        if($value !== Util::TYPES_ROLE["ADMIN"]) {
          array_push($viewData['typeroles'], $value);
        }
      }

      $localities = DB::select("select * from localities where type = 2 order by localities.name asc");
      $countries = Country::all();
      $viewData["countries"] = $countries;
      $viewData["countriesJson"] = json_encode($countries);

      $viewData["bjId"] = '';
      foreach ($viewData["countries"] as $country) {
        if(strtoupper($country->iso2) == "BJ"){
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
      return view('admin.'.strtolower(__FUNCTION__), [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Inscription', 'pagecardtilte' => '',
      ]);
    }

    public function registrationPost (Request $request){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( $this->isConnected() ) {
        //return redirect(config('app.url').'/admin/dashboard')->with($alert);
        return $this->redirect ($request, $alert);
      }

      $request->validate([
        'prenom' => 'required|max:255',
        'nom' => 'required|max:255',
        'country' => 'required',
        'phonecountry' => 'required',
        'phone' => 'required|min:08|max:13',
        'email' => 'required|email|unique:users',
        'password' => ['required', 'confirmed', Password::min(8)
          ->mixedCase()
          ->letters()
          ->numbers()
          ->symbols()],
        'profil' => 'required',
        'vuesmoyen' => 'required|numeric|min:1',
        'termes' => 'accepted',
      ]);

      $requestData = $request->all();

      $processingTransaction = false;
      try {
        $date = gmdate('Y-m-d H:i:s');
        $uId = $this->getId();
        $user = [
          'id' => $uId,
          'lastname' => $request->nom,
          'firstname' => $request->prenom,
          'email' => $request->email,
          'password' => Hash::make($request->password),
          'enabled' => true,
          'twofa_enabled' => false,
          'twofa_code' => null,
          'email_verified_at' => null,
          'lastconnection' => null,
          'country_id' => (!empty($request->country) ? $request->country : null),
          'locality_id' => (!empty($request->locality) ? $request->locality : null),
          'study_id' => (!empty($request->study) ? $request->study : null),
          'lang_id' => (!empty($request->lang) ? $request->lang : null),
          'occupation_id' => (!empty($request->occupation) ? $request->occupation : null),
          'occupation' => (!empty($request->autre_occupation) ? $request->autre_occupation : null),
          'phonecountry_id' => $request->phonecountry,
          'phone' => $request->phone,
          'vuesmoyen' => $request->vuesmoyen
        ];
        DB::beginTransaction();$processingTransaction = true;
        $user = User::create($user);

        $roles = Role::All();
        foreach ($roles as $role) {
          if($role->typerole == $request->profil) {
            $right_role = [
              'role_id' => $role->id,
              'user_id' => $uId,
              'updated_at' => $date,
              'created_at' => $date,
            ];
            DB::table('role_user')->insert($right_role);
          }
        }

        if($request->profil === Util::TYPES_ROLE["DIFFUSEUR"]) {
          $categories = Category::all();
          foreach ($categories as $category) {
            if (!empty($requestData["c_" . $category->id])) {
              $category_user = [
                'category_id' => $category->id,
                'user_id' => $uId,
                'updated_at' => $date,
                'created_at' => $date,
              ];
              DB::table('category_user')->insert($category_user);
            }
          }

          $contenttypes = Contenttype::all();
          foreach ($contenttypes as $contenttype) {
            if (!empty($requestData["ct_" . $contenttype->id])) {
              $contenttype_user = [
                'contenttype_id' => $contenttype->id,
                'user_id' => $uId,
                'updated_at' => $date,
                'created_at' => $date,
              ];
              DB::table('contenttype_user')->insert($contenttype_user);
            }
          }

        }

        DB::commit();$processingTransaction = false;

        $token = [
          'type' => 'registration',
          'date' => gmdate("YmdHis"),
          'mail' => $request->email,
          'id' => $user->id
        ];
        //https://github.com/firebase/php-jwt
        $token = JWT::encode($token, Util::JWTKEY, 'HS256');

        $this->sendDataToQueue([
          'recipient' => $request->email,
          'type' => 'registration',
          'subject' => 'Inscription sur WhatsPAY',
          'lastname' => $request->lastname,
          'firstname' => $request->firstname,
          'token' => $token,
          'url' => config('app.url'),
        ], Util::MAILSENDER_QUEUE);

        return redirect(config('app.url').'/admin/login')->with([
          'message' => 'Inscription enregistrée avec succes. Veuillez bien verifier vos mail afin de valider votre compte.',
          'type' => 'success' //danger warning info
        ]);
      }catch (\Exception $exception) {
        if($processingTransaction) {
          DB::rollBack();
        }
        $alert["type"] = "danger";
        $alert["message"] = "Une erreur est survenue au cours de l'enregistrement, veuillez contacter l'administrateur.";
      }


      $viewData['typeroles'] = [];
      foreach (Util::TYPES_ROLE as $key => $value){
        if($value !== Util::TYPES_ROLE["ADMIN"]) {
          array_push($viewData['typeroles'], $value);
        }
      }
      $requestData = $request->all();
      $localities = DB::select("select * from localities where type = 2 order by localities.name asc");
      $countries = Country::all();
      $viewData["countries"] = $countries;
      $viewData["countriesJson"] = json_encode($countries);
      $viewData["bjId"] = '';
      foreach ($viewData["countries"] as $country) {
        if(strtoupper($country->iso2) == "BJ"){
          $viewData["bjId"] = $country->id;
        }
      }
      $viewData["occupations"] = Occupation::all();
      $viewData["localities"] = $localities;
      $viewData["localitiesJson"] = json_encode($localities);
      $viewData["profil"] = (!empty($requestData["profil"]) ? strtoupper($requestData["profil"]) : '');
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
      return view('admin.registrationget', [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Inscription', 'pagecardtilte' => '',
      ]);
    }

    public function loginGet (Request $request){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        if ( $this->isConnected() ) {
          //return redirect(config('app.url').'/admin/dashboard')->with($alert);
          return $this->redirect ($request, $alert);
        }

        $viewData['typeroles'] = [];
        foreach (Util::TYPES_ROLE as $key => $value){
          //if($value !== Util::TYPES_ROLE["ADMIN"]) {
            array_push($viewData['typeroles'], $value);
          //}
        }

        $this->setViewData($request, $viewData);
        return view('admin.'.strtolower(__FUNCTION__), [
            'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Connexion', 'pagecardtilte' => '',
        ]);
    }

    public function loginPost (Request $request){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        if ( $this->isConnected() ) {
            //return redirect(config('app.url').'/admin/dashboard')->with($alert);
            return $this->redirect ($request, $alert);
        }

        $request->validate([
          'profil' => 'required',
          'email' => 'required|email',
          'password' => 'required|min:8',
        ]);

        $rememberMe = false;
        if(isset($request->rememberMe)){
            $rememberMe = true;
        }
        
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], $rememberMe)) {

            if(Auth::user()->enabled == true){
                if(!empty(Auth::user()->email_verified_at)){
                    if(!empty(Auth::user()->lastconnection)){
                        if(Auth::user()->twofa_enabled == false){

                            $profil = DB::select("select role_user.role_id from role_user left join roles on role_user.role_id = roles.id where role_user.user_id = :user_id and roles.typerole = :typerole ;", [
                              'user_id' => Auth::user()->id,
                              'typerole' => $request->profil
                            ]);

                            if(count($profil) == 1){
                              $request->session()->put('user', Auth::user()->email);
                              $request->session()->put('userid', Auth::user()->id);
                              $request->session()->put('userfirstname', Auth::user()->firstname);
                              $request->session()->put('userlastname', Auth::user()->lastname);
                              $request->session()->put('userprofile', $request->profil);
                              $request->session()->put('userrights', json_encode([]));
                              $this->lastConnection(Auth::user()->id);
                              return $this->redirect ($request, $alert);
                            }else{
                              $alert["type"] = "danger";
                              $alert["message"] = "Profil non identifie.";
                              return $this->logout($request, $alert);
                            }
                        }

                        /*

                        else{
                            $token = [
                                'rememberMe' => $rememberMe,
                                'type' => 'twofa_check',
                                'date' => gmdate("YmdHis"),
                                'mail' => Auth::user()->email,
                                'id' => Auth::user()->id
                            ];
                            //https://github.com/firebase/php-jwt
                            $token = JWT::encode($token, Util::JWTKEY, 'HS256');
                            $this->flushSession($request);

                            $alert["type"] = "warning";
                            $alert["message"] = "Two Factor Authentication is enabled on this account. Please enter the OTP code.";
                            return redirect(config('app.url').'/admin/twofa_auth/'.$token)->with($alert);
                        }

                        */

                    }else{
                        $token = [
                            'type' => 'forgotten_password',
                            'date' => gmdate("YmdHis"),
                            'mail' => Auth::user()->email,
                            'id' => Auth::user()->id
                        ];
                        //https://github.com/firebase/php-jwt
                        $token = JWT::encode($token, Util::JWTKEY, 'HS256');
                        $this->flushSession($request);

                        $alert["type"] = "warning";
                        $alert["message"] = "Ceci est votre premiere connexion. Veuillez bien mettre a jour votre mot de passe.";
                        return redirect(config('app.url').'/admin/password_recovery/'.$token)->with($alert);
                    }
                }else{
                    $alert["type"] = "danger";
                    $alert["message"] = "Compte non verifie.";
                    return $this->logout($request, $alert);
                }
            }else{
                $alert["type"] = "danger";
                $alert["message"] = "Compte inactif";
                return $this->logout($request, $alert);
            }
        } else {
            $alert["type"] = "danger";
            $alert["message"] = "Identifiants incorrects";
            return $this->logout($request, $alert);
        }
    }

    public function password_recoveryGet (Request $request, $token){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        if ( $this->isConnected() ) {
            //return redirect(config('app.url').'/admin/dashboard')->with($alert);
            return $this->redirect ($request, $alert);
        }

        if(empty($token) ){
            $alert["message"] = "Requete de recuperation non identifiee [1]";
            $alert["type"] = "danger";
            return redirect(config('app.url').'/admin/login')->with($alert);
        }else{
            try {
                $decodedToken = JWT::decode($token, new Key(Util::JWTKEY, 'HS256'));
                if(!empty($decodedToken->mail) && !empty($decodedToken->type) && $decodedToken->type == 'forgotten_password'){
                    $viewData["email"] = $decodedToken->mail;
                }else{
                    $alert["message"] = "Requete de recuperation non identifiee  [2]";
                    $alert["type"] = "danger";
                }
            }catch (Exception $exception){
                $alert["message"] = "Une erreur est survenue lors de la verification de la requete de recuperation";
                $alert["type"] = "danger";
            }
        }

        $this->setViewData($request, $viewData);
        return view('admin.'.strtolower(__FUNCTION__), [
            'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Recuperation de mot de passe', 'pagecardtilte' => '',
        ]);
    }

    public function password_recoveryPost (Request $request, $token){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        if ( $this->isConnected() ) {
            //return redirect(config('app.url').'/admin/dashboard')->with($alert);
            return $this->redirect ($request, $alert);
        }

        $request->validate([
          'password' => ['required', 'confirmed', Password::min(8)
            ->mixedCase()
            ->letters()
            ->numbers()
            ->symbols()],
        ]);

        $processingTransaction = false;
        try {
            $decodedToken = JWT::decode($token, new Key(Util::JWTKEY, 'HS256'));
            if(!empty($decodedToken->mail)){
                $email = $decodedToken->mail;
                $userExist = DB::select("select * from users where email = :email", [
                    'email' => $email
                ]);
                if(count($userExist) == 1){
                    if(!empty($userExist[0]->enabled)){
                      $user = [
                        'password' => Hash::make($request->password),
                        'lastconnection' => gmdate('Y-m-d H:i:s')
                      ];
                      DB::beginTransaction();$processingTransaction = true;
                      User::where('id', $userExist[0]->id)->update($user);
                      DB::commit();$processingTransaction = false;


                      $this->sendDataToQueue([
                        'recipient' => $email,
                        'type' => 'password_recovery',
                        'subject' => 'Mot de passe WhatsPAY mis a jour',
                        'lastname' => $userExist[0]->lastname,
                        'firstname' => $userExist[0]->firstname,
                        'url' => config('app.url'),
                      ], Util::MAILSENDER_QUEUE);

                      return redirect(config('app.url').'/admin/login')->with([
                        'message' => 'Le mot de passe de votre compte ('.$email.') a ete mis a jour avec succes',
                        'type' => 'success' //danger warning info
                      ]);

                    }else{
                      $alert["type"] = "danger";
                      $alert["message"] = "Opération impossible. Compte inactif";
                    }
                }else{
                    $alert["type"] = "warning";
                    $alert["message"] = "Compte non identifie";
                }
            }else{
                $alert["message"] = "Requete de recuperation non identifiee  [2]";
                $alert["type"] = "danger";
            }
        }catch (Exception $exception){
            if($processingTransaction) {
                DB::rollBack();
            }
            $alert["message"] = "Une erreur est survenue lors de la verification de la requete de recuperation";
            $alert["type"] = "danger";
        }

        return redirect(config('app.url').'/password_recovery/'.$token)->with($alert);
    }

    public function twofa_authGet (Request $request){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        if ( $this->isConnected() ) {
            //return redirect(config('app.url').'/admin/dashboard')->with($alert);
            return $this->redirect ($request, $alert);
        }

        $this->setViewData($request, $viewData);
        return view('admin.'.strtolower(__FUNCTION__), [
            'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 'pagetilte' => '', 'pagecardtilte' => '',
        ]);
    }

    public function twofa_authPost (Request $request){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        if ( $this->isConnected() ) {
            //return redirect(config('app.url').'/admin/dashboard')->with($alert);
            return $this->redirect ($request, $alert);
        }

        $this->setViewData($request, $viewData);
        return view('admin.'.strtolower(__FUNCTION__), [
            'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 'pagetilte' => '', 'pagecardtilte' => '',
        ]);
    }

    public function dashboardGet (Request $request){
        $viewData = []; $alert = []; $this->setAlert($request, $alert);
        if ( !$this->isConnected() ) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }

        $this->setViewData($request, $viewData);
        return view('admin.'.strtolower(__FUNCTION__), [
            'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Dashboard', 'pagecardtilte' => 'Bienvenue sur WhatsPAY | Admin',
        ]);
    }
public function tasksGet(Request $request)
{
    $viewData = []; 
    $alert = []; 
    $this->setAlert($request, $alert);
    
    if (!$this->isConnected()) {
        $url = URL::route('admin.login', [], true, config('app.url'));
        return redirect()->to($url)->with($alert);
    }

    $blade = "";
    $userProfile = $request->session()->get('userprofile');
    $userId = $request->session()->get('userid');

    switch ($userProfile) {
        case "ADMIN":
            // Récupérer toutes les tâches pour les administrateurs
            $viewData["tasks"] = Task::orderBy('created_at', 'desc')->get();
            $blade = "admin_tasks";
            break;
            
        case "ANNONCEUR":
            // Récupérer seulement les tâches créées par cet annonceur
            $viewData["tasks"] = Task::where('client_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
            $blade = "client_tasks";
            break;
            
        case "DIFFUSEUR":
            // Récupérer les tâches assignées à ce diffuseur avec les informations d'assignation
            $viewData["tasks"] = Assignment::where('agent_id', $userId)
                ->with('task')  // Chargement eager de la relation task
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($assignment) {
                    // Fusionner les propriétés de la tâche avec les informations d'assignation
                    $taskData = $assignment->task->toArray();
                    $taskData['assignment_id'] = $assignment->id;
                    $taskData['assignment_status'] = $assignment->status;
                    return (object) $taskData;
                });
            $blade = "agent_tasks";
            break;
    }

    $this->setViewData($request, $viewData);
    
    return view('admin.' . $blade, [
        'alert' => $alert, 
        'viewData' => $viewData, 
        'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 
        'pagetilte' => 'Dashboard', 
        'pagecardtilte' => 'Bienvenue sur WhatsPAY | Admin',
    ]);
}

    public function taskGet (Request $request, $id){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( !$this->isConnected() ) {
        return redirect(config('app.url').'/admin/login')->with($alert);
      }

      $viewData["title"] = "Nouvelle tache";
      $viewData["subtitle"] = "Veuillez bien renseigner les informations relatives &agrave; la nouvelle tache";

      switch ($id){
        case "new":
          $viewData["task"] = new Task();
          break;
        default:
          $task = DB::select("select * from tasks where id = :id", [ 'id' => $id ]);
          if(count($task) == 0){
            $viewData["task"] = new Task();
          }else{
            $viewData["task"] = $task[0];
            $viewData["title"] = "Fiche tache";
            $viewData["subtitle"] = "Ci dessous les informations relatives &agrave; la tache";
          }
          break;
      }

      $viewData["categories"] = Category::all();
      $this->setViewData($request, $viewData);
      return view('admin.task', [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => ''.$viewData["title"] , 'pagecardtilte' => 'Bienvenue sur WhatsPAY | Admin',
      ]);
    }

    public function taskPost (Request $request, $id){
    $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( !$this->isConnected() ) {
        return redirect(config('app.url').'/admin/login')->with($alert);
      }

    $request->validate([
      'name' => 'required|max:255',
      'description' => 'required',
      'budget' => 'required|numeric|min:1000',
      'startdate' => 'required|date_format:d/m/Y',
      'enddate' => 'required|date_format:d/m/Y',
      'taskfiles' => 'required',
    ]);

    $requestData = $request->all();

    $startdate = explode('/',$request->startdate);
    $startdate = $startdate[2].'-'.$startdate[1].'-'.$startdate[0];

    $enddate = explode('/',$request->enddate);
    $enddate = $enddate[2].'-'.$enddate[1].'-'.$enddate[0];


      $processingTransaction = false;
    try {
      $date = gmdate('Y-m-d H:i:s');
      $tId = $this->getId();
      $task = [
        'id' =>$tId,
        'name' => $request->name,
        'descriptipon' => $request->description,
        'files' => $request->taskfiles,
        'status' => Util::TASKS_STATUSES["PENDING"],
        'client_id' => $request->session()->get('userid'),
        'validation_date' => null,
        'validateur_id' => null,
        'startdate' => $startdate,
        'enddate' => $enddate,
        'budget' => $request->budget
      ];
      DB::beginTransaction();$processingTransaction = true;
      $task = Task::create($task);

      $categories = Category::all();
      foreach ($categories as $category) {
        if (!empty($requestData["c_" . $category->id])) {
          $category_task = [
            'category_id' => $category->id,
            'user_id' => $tId,
            'updated_at' => $date,
            'created_at' => $date,
          ];
          DB::table('category_task')->insert($category_task);
        }
      }

      DB::commit();$processingTransaction = false;

      return redirect(config('app.url').'/admin/tasks')->with([
        'message' => 'Demande enregistrée avec succes.',
        'type' => 'success' //danger warning info
      ]);
    }catch (\Exception $exception) {
      if($processingTransaction) {
        DB::rollBack();
      }
      $alert["type"] = "danger";
      $alert["message"] = "Une erreur est survenue au cours de l'enregistrement, veuillez contacter l'administrateur.";
    }


      $viewData["title"] = "Nouvelle tache";
      $viewData["subtitle"] = "Veuillez bien renseigner les informations relatives &agrave; la nouvelle tache";

      switch ($id){
        case "new":
          $viewData["task"] = new Task();
          break;
        default:
          $task = DB::select("select * from tasks where id = :id", [ 'id' => $id ]);
          if(count($task) == 0){
            $viewData["task"] = new Task();
          }else{
            $viewData["task"] = $task[0];
            $viewData["title"] = "Fiche tache";
            $viewData["subtitle"] = "Ci dessous les informations relatives &agrave; la tache";
          }
          break;
      }

    $viewData["categories"] = Category::all();
    $this->setViewData($request, $viewData);
    return view('admin.task', [
      'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
      'title' => 'WhatsPAY | Admin', 'pagetilte' => ''.$viewData["title"] , 'pagecardtilte' => 'Bienvenue sur WhatsPAY | Admin',
    ]);
  }

    public function whatsappnumbersGet (Request $request){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( !$this->isConnected() ) {
        return redirect(config('app.url').'/admin/login')->with($alert);
      }

      $pagetilte = "";
      $pagecardtilte = "";

      $this->setViewData($request, $viewData);

      $countries = Country::all();
      $viewData["countries"] = $countries;
      $viewData["countriesJson"] = json_encode($countries);

      $viewData["bjId"] = '';
      foreach ($viewData["countries"] as $country) {
        if(strtoupper($country->iso2) == "BJ"){
          $viewData["bjId"] = $country->id;
        }
      }

      $viewData["phones"] = Phone::where('user_id', $viewData['userid'])->get();
      $viewData["statuses"] = Util::PHONE_STATUSES;

      return view('admin.whatsappnumbers', [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Numeros Whatsapp', 'pagecardtilte' => 'Liste des numeros Whatsapp',
      ]);
    }

    public function usersGet (Request $request, $group){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( !$this->isConnected() ) {
        return redirect(config('app.url').'/admin/login')->with($alert);
      }

      if(!in_array($group, ["admin","annonceur","diffuseur"])){
        $alert["type"] = "danger";
        $alert["message"] = "Le lien recherche n'est pas valide";
        return redirect(config('app.url').'/admin/dashboard')->with($alert);
      }

      $pagetilte = "";
      $pagecardtilte = "";

      $requestData = $request->all();

      $sql = "select 
  users.* , 
  studies.name as study, 
  langs.name as lang, 
  countries.name as country, 
  localities.name as locality, 
  occupations.name as profession,
  string_agg(DISTINCT substr(categories.name, 1, 10), ', ') as category,
  string_agg(DISTINCT contenttypes.name, ', ') as contenttype
              from users 
              left join countries on countries.id = users.country_id  
              left join localities on localities.id = users.locality_id  
              left join role_user on role_user.user_id = users.id  
              left join roles on roles.id = role_user.role_id
              left join studies on studies.id = users.study_id
              left join langs on langs.id = users.lang_id
              left join occupations on occupations.id = users.occupation_id
              left join category_user on category_user.user_id = users.id
              left join categories on categories.id = category_user.category_id
              left join contenttype_user on contenttype_user.user_id = users.id
              left join contenttypes on contenttypes.id = contenttype_user.contenttype_id
              where roles.typerole = :typerole";

      foreach (["filtre_country","filtre_locality"] as $item){
        $viewData[$item] = "";
      }
      foreach (["filtre_occupation","filtre_study","filtre_category","filtre_category","filtre_contenu","filtre_lang"] as $item){
        $viewData[$item] = [];
      }

      $filtre_country = !empty($requestData["filtre_country"]) ? $requestData["filtre_country"] : '';
      $filtre_locality = !empty($requestData["filtre_locality"]) ? $requestData["filtre_locality"] : '';
      $filtre_occupation = !empty($requestData["filtre_occupation"]) ? '\''.implode('\',\'', $requestData["filtre_occupation"]).'\'' : '';
      $filtre_study = !empty($requestData["filtre_study"]) ? '\''.implode('\',\'', $requestData["filtre_study"]).'\'' : '';
      $filtre_category = !empty($requestData["filtre_category"]) ? '\''.implode('\',\'', $requestData["filtre_category"]).'\'' : '';
      $filtre_contenu = !empty($requestData["filtre_contenu"]) ? '\''.implode('\',\'', $requestData["filtre_contenu"]).'\'' : '';
      $filtre_lang = !empty($requestData["filtre_lang"]) ? '\''.implode('\',\'', $requestData["filtre_lang"]).'\'' : '';

      if(!empty($filtre_country)){ $sql .= " and users.country_id = '".$filtre_country."'"; }
      if(!empty($filtre_locality)){ $sql .= " and users.locality_id = '".$filtre_locality."'"; }
      if(!empty($filtre_lang)){ $sql .= " and users.lang_id in (".$filtre_lang.")"; }
      if(!empty($filtre_category)){ $sql .= " and category_user.category_id in (".$filtre_category.")"; }
      if(!empty($filtre_contenu)){ $sql .= " and contenttype_user.contenttype_id in (".$filtre_contenu.")"; }
      if(!empty($filtre_occupation)){
        if($this->contains($filtre_occupation, "other")){
          $filtre_occupation2 = str_replace(",'other'", "", $filtre_occupation);
          $filtre_occupation2 = str_replace("'other'", "", $filtre_occupation2);
          $sql .= " and ( users.occupation_id in (".$filtre_occupation2.") or ( users.occupation_id is null and users.occupation is not null) )";
        }else{
          $sql .= " and users.occupation_id in (".$filtre_occupation.")";
        }
      }
      if(!empty($filtre_study)){ $sql .= " and users.study_id in (".$filtre_study.")"; }

      $u = implode(', users.',['id', 'lastname', 'firstname', 'email', 'password', 'enabled', 'twofa_enabled', 'twofa_code', 'email_verified_at', 'lastconnection', 'country_id', 'phonecountry_id', 'phone', 'vuesmoyen', 'locality_id', 'lang_id', 'study_id', 'occupation', 'occupation_id']);
      $sql .= " group by users.".$u.", study, lang, country, locality, profession";

      switch ($group){
        case "admin":
          $pagetilte = "Admins";
          $pagecardtilte = "Liste des Admins";
          $viewData["items"] = DB::select($sql, [
            'typerole' => Util::TYPES_ROLE[strtoupper($group)]
          ]);
          break;
        case "annonceur":
          $pagetilte = "Annonceurs";
          $pagecardtilte = "Liste des Annonceurs";
          $viewData["items"] = DB::select($sql, [
            'typerole' => Util::TYPES_ROLE[strtoupper($group)]
          ]);
          break;
        case "diffuseur":
          $pagetilte = "Diffuseurs";
          $pagecardtilte = "Liste des Diffuseurs";
          $viewData["items"] = DB::select($sql, [
            'typerole' => Util::TYPES_ROLE[strtoupper($group)]
          ]);
          break;
      }



      $viewData["vuesmoyen"] = 0;
      foreach ($viewData["items"] as $item){
        $viewData["vuesmoyen"] += $item->vuesmoyen;
      }
      //$viewData["nblines"] = count($viewData["items"]);

      $countries = Country::all();
      $viewData["countries"] = $countries;
      $viewData["countriesJson"] = json_encode($countries);

      $viewData["bjId"] = '';
      foreach ($viewData["countries"] as $country) {
        if(strtoupper($country->iso2) == "BJ"){
          $viewData["bjId"] = $country->id;
        }
      }

      $viewData["occupations"] = Occupation::all();

      $localities = DB::select("select * from localities where type = 2 order by localities.name asc");
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

      foreach ($requestData as $key => $value) {
        $viewData[$key] = $value;
      }

      //dd($viewData);

      $this->setViewData($request, $viewData);
      return view('admin.'.$group, [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => $pagetilte, 'pagecardtilte' => $pagecardtilte,
      ]);
    }

    public function usersPost (Request $request, $group){
      $viewData = []; $alert = []; $this->setAlert($request, $alert);
      if ( !$this->isConnected() ) {
        return redirect(config('app.url').'/admin/login')->with($alert);
      }

      $requestData = $request->all();

      $this->setViewData($request, $viewData);
      return view('admin.'.strtolower(__FUNCTION__), [
        'alert' => $alert, 'viewData' => $viewData, 'version' => gmdate("YmdHis"),
        'title' => 'WhatsPAY | Admin', 'pagetilte' => 'Inscription', 'pagecardtilte' => '',
      ]);
    }


}


