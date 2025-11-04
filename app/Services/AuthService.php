<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Category;
use App\Models\Contenttype;
use App\Models\Role;
use App\Models\User;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
// Par celui-ci:
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;

class AuthService
{
    use Utils;

public function login($email, $password, $rememberMe = false, $profil = null)
{
    $result = [
        'success' => false,
        'message' => 'Identifiants incorrects'
    ];

    if (Auth::attempt([
        'email' => $email,
        'password' => $password
    ], $rememberMe)) {

        if (Auth::user()->enabled == true) {
            if (!empty(Auth::user()->email_verified_at)) {
                if (Auth::user()->twofa_enabled == false) {

                    $userProfiles = DB::select("select role_user.role_id from role_user left join roles on role_user.role_id = roles.id where role_user.user_id = :user_id and roles.typerole = :typerole ;", [
                        'user_id' => Auth::user()->id,
                        'typerole' => $profil
                    ]);

                    if (count($userProfiles) == 1) {
                        request()->session()->put('user', Auth::user()->email);
                        request()->session()->put('userid', Auth::user()->id);
                        request()->session()->put('userfirstname', Auth::user()->firstname);
                        request()->session()->put('userlastname', Auth::user()->lastname);
                        request()->session()->put('userprofile', $profil);
                        request()->session()->put('userrights', json_encode([]));
                        $this->lastConnection(Auth::user()->id);
                        
                        $result['success'] = true;
                        $result['message'] = 'Connexion réussie';
                        return $result;
                    } else {
                        $result['message'] = 'Profil non identifié';
                        $this->logout(request());
                    }
                }
            } else {
                $result['message'] = 'Compte non vérifié';
                $this->logout(request());
            }
        } else {
            $result['message'] = 'Compte inactif';
            $this->logout(request());
        }
    }

    return $result;
}

    public function register(array $userData)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'enregistrement'
        ];

        $processingTransaction = false;
        
        try {
            $date = gmdate('Y-m-d H:i:s');
            $uId = $this->getId();
            
            // Préparation des données utilisateur
            $user = [
                'id' => $uId,
                'lastname' => $userData['lastname'],
                'firstname' => $userData['firstname'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'enabled' => true,
                'twofa_enabled' => false,
                'twofa_code' => null,
                'email_verified_at' => null,
                'lastconnection' => null,
                'country_id' => (!empty($userData['country_id']) ? $userData['country_id'] : null),
                'locality_id' => (!empty($userData['locality_id']) ? $userData['locality_id'] : null),
                'study_id' => (!empty($userData['study_id']) ? $userData['study_id'] : null),
                'lang_id' => (!empty($userData['lang_id']) ? $userData['lang_id'] : null),
                'occupation_id' => (!empty($userData['occupation_id']) ? $userData['occupation_id'] : null),
                'occupation' => (!empty($userData['occupation']) ? $userData['occupation'] : null),
                'phonecountry_id' => $userData['phonecountry_id'],
                'phone' => $userData['phone'],
            ];
            
            // Ajouter vuesmoyen uniquement si le profil est DIFFUSEUR
// Dans la méthode register
// Ajouter vuesmoyen uniquement si le profil est DIFFUSEUR
if ($userData['profil'] === Util::TYPES_ROLE["DIFFUSEUR"]) {
    if (empty($userData['vuesmoyen'])) {
        $result['message'] = 'Le nombre de vues moyen est requis pour les diffuseurs';
        return $result;
    }
    $user['vuesmoyen'] = $userData['vuesmoyen'];
} else {
    // Valeur par défaut pour les non-diffuseurs
    $user['vuesmoyen'] = 0;
}

            DB::beginTransaction();
            $processingTransaction = true;
            
            // Créer l'utilisateur
            $user = User::create($user);

            // Attribuer le rôle
            $roles = Role::all();
            foreach ($roles as $role) {
                if ($role->typerole == $userData['profil']) {
                    $right_role = [
                        'role_id' => $role->id,
                        'user_id' => $uId,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ];
                    DB::table('role_user')->insert($right_role);
                }
            }

            // Attribuer les catégories et types de contenu pour les diffuseurs
            if ($userData['profil'] === Util::TYPES_ROLE["DIFFUSEUR"]) {
                if (!empty($userData['categories'])) {
                    foreach ($userData['categories'] as $categoryId) {
                        $category_user = [
                            'category_id' => $categoryId,
                            'user_id' => $uId,
                            'updated_at' => $date,
                            'created_at' => $date,
                        ];
                        DB::table('category_user')->insert($category_user);
                    }
                }

                if (!empty($userData['contentTypes'])) {
                    foreach ($userData['contentTypes'] as $contentTypeId) {
                        $contenttype_user = [
                            'contenttype_id' => $contentTypeId,
                            'user_id' => $uId,
                            'updated_at' => $date,
                            'created_at' => $date,
                        ];
                        DB::table('contenttype_user')->insert($contenttype_user);
                    }
                }
            }

            // Générer code de vérification à 8 caractères (chiffres et lettres)
            $verificationCode = strtoupper(Str::random(8));
            
            // Sauvegarder code de vérification dans DB
            User::where('id', $uId)->update([
                'email_verification_code' => $verificationCode,
                'email_verification_code_expiry' => gmdate('Y-m-d H:i:s', strtotime('+24 hours'))
            ]);

            DB::commit();
            $processingTransaction = false;

            // Envoyer email avec code de vérification
         
Mail::to($userData['email'])->send(new Email([
    'type' => 'registration',
    'subject' => 'Inscription sur WhatsPAY - Code de vérification',
    'lastname' => $userData['lastname'],
    'firstname' => $userData['firstname'],
    'verification_code' => $verificationCode,
    'url' => config('app.url'),
]));


            $result['success'] = true;
            $result['message'] = 'Inscription enregistrée avec succès';
            return $result;
            
        } catch (\Exception $exception) {
            if ($processingTransaction) {
                DB::rollBack();
            }
            $result['message'] = 'Une erreur est survenue: ' . $exception->getMessage();
            return $result;
        }
    }

    public function verifyAccount($email, $verificationCode)
    {
        $result = [
            'success' => false,
            'message' => 'Code de vérification invalide'
        ];

        $user = User::where('email', $email)
            ->where('email_verification_code', $verificationCode)
            ->where('email_verified_at', null)
            ->first();

        if (!$user) {
            return $result;
        }

        // Vérifier expiration du code
        if (strtotime($user->email_verification_code_expiry) < time()) {
            $result['message'] = 'Code de vérification expiré';
            return $result;
        }

        // Valider le compte
        $user->email_verified_at = gmdate('Y-m-d H:i:s');
        $user->email_verification_code = null;
        $user->email_verification_code_expiry = null;
        $user->save();

        $result['success'] = true;
        $result['message'] = 'Compte vérifié avec succès';
        return $result;
    }

    public function sendResetCode($email)
    {
        $result = [
            'success' => false,
            'message' => 'Email non trouvé'
        ];

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return $result;
        }

        if (!$user->enabled) {
            $result['message'] = 'Compte inactif';
            return $result;
        }

        if (!$user->email_verified_at) {
            $result['message'] = 'Compte non vérifié';
            return $result;
        }

        // Générer code de réinitialisation à 8 caractères (chiffres et lettres)
        $resetCode = strtoupper(Str::random(8));
        
        // Sauvegarder code dans DB
        $user->password_reset_code = $resetCode;
        $user->password_reset_code_expiry = gmdate('Y-m-d H:i:s', strtotime('+1 hour'));
        $user->save();

        // Envoyer email avec code
        $this->sendDataToQueue([
            'recipient' => $email,
            'type' => 'password_reset',
            'subject' => 'Réinitialisation de votre mot de passe WhatsPAY',
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'reset_code' => $resetCode,
            'url' => config('app.url'),
        ], Util::MAILSENDER_QUEUE);

        $result['success'] = true;
        $result['message'] = 'Code de réinitialisation envoyé';
        return $result;
    }

    public function resetPassword($email, $resetCode, $newPassword)
    {
        $result = [
            'success' => false,
            'message' => 'Code de réinitialisation invalide'
        ];

        $user = User::where('email', $email)
            ->where('password_reset_code', $resetCode)
            ->first();

        if (!$user) {
            return $result;
        }

        // Vérifier expiration du code
        if (strtotime($user->password_reset_code_expiry) < time()) {
            $result['message'] = 'Code de réinitialisation expiré';
            return $result;
        }

        // Réinitialiser mot de passe
        $user->password = Hash::make($newPassword);
        $user->password_reset_code = null;
        $user->password_reset_code_expiry = null;
        $user->lastconnection = gmdate('Y-m-d H:i:s');
        $user->save();

        // Envoyer notification
        $this->sendDataToQueue([
            'recipient' => $email,
            'type' => 'password_recovery',
            'subject' => 'Mot de passe WhatsPAY mis à jour',
            'lastname' => $user->lastname,
            'firstname' => $user->firstname,
            'url' => config('app.url'),
        ], Util::MAILSENDER_QUEUE);

        $result['success'] = true;
        $result['message'] = 'Mot de passe réinitialisé avec succès';
        return $result;
    }

    public function logout(Request $request)
    {
        if ($request->session()->has('user')) {
            $request->session()->forget('user');
            $request->session()->forget('userid');
            $request->session()->forget('userfirstname');
            $request->session()->forget('userlastname');
            $request->session()->forget('userprofile');
            $request->session()->forget('userrights');
        }
        
        Auth::logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function lastConnection($id)
    {
        User::where('id', $id)->update([
            'lastconnection' => gmdate('Y-m-d H:i:s')
        ]);
    }
}