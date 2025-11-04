<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Assignment;
use App\Models\Category;
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    use Utils;
    
    public function getUsers($filters = [])
    {
        // Démarrer avec une requête de base
        $query = User::select('users.*')
            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('category_user', 'category_user.user_id', '=', 'users.id')
            ->leftJoin('categories', 'categories.id', '=', 'category_user.category_id')
            ->leftJoin('countries', 'countries.id', '=', 'users.country_id')
            ->leftJoin('localities', 'localities.id', '=', 'users.locality_id')
            ->leftJoin('studies', 'studies.id', '=', 'users.study_id')
            ->leftJoin('langs', 'langs.id', '=', 'users.lang_id')
            ->leftJoin('occupations', 'occupations.id', '=', 'users.occupation_id')
            ->distinct();
        
        // Appliquer les filtres
        if (!empty($filters['profile'])) {
            $query->where('roles.typerole', '=', $filters['profile']);
        }
        
        if (!empty($filters['country_id'])) {
            $query->where('users.country_id', '=', $filters['country_id']);
        }
        
        if (!empty($filters['locality_id'])) {
            $query->where('users.locality_id', '=', $filters['locality_id']);
        }
        
        if (!empty($filters['category_id'])) {
            $query->where('category_user.category_id', '=', $filters['category_id']);
        }
        
        // Récupérer les IDs des utilisateurs filtrés
        $userIds = $query->pluck('users.id')->toArray();
        
        // Si aucun utilisateur ne correspond aux filtres, retourner un tableau vide
        if (empty($userIds)) {
            return [];
        }
        
        // Récupérer toutes les données pour ces utilisateurs
        $users = DB::table('users')
            ->select([
                'users.*',
                'studies.name as study',
                'langs.name as lang',
                'countries.name as country',
                'localities.name as locality',
                'occupations.name as profession',
            ])
            ->leftJoin('countries', 'countries.id', '=', 'users.country_id')
            ->leftJoin('localities', 'localities.id', '=', 'users.locality_id')
            ->leftJoin('studies', 'studies.id', '=', 'users.study_id')
            ->leftJoin('langs', 'langs.id', '=', 'users.lang_id')
            ->leftJoin('occupations', 'occupations.id', '=', 'users.occupation_id')
            ->whereIn('users.id', $userIds)
            ->orderByDesc('users.created_at')
            ->get();
        
        // Ajouter les catégories et types de contenu agrégés
        $userList = [];
        foreach ($users as $user) {
            // Récupérer les catégories pour cet utilisateur
            $categories = DB::table('category_user')
                ->join('categories', 'categories.id', '=', 'category_user.category_id')
                ->where('category_user.user_id', $user->id)
                ->pluck('categories.name')
                ->map(function ($name) {
                    return Str::limit($name, 10);
                })
                ->implode(', ');
            
            // Récupérer les types de contenu pour cet utilisateur
            $contentTypes = DB::table('contenttype_user')
                ->join('contenttypes', 'contenttypes.id', '=', 'contenttype_user.contenttype_id')
                ->where('contenttype_user.user_id', $user->id)
                ->pluck('contenttypes.name')
                ->implode(', ');
            
            // Ajouter à l'objet utilisateur
            $user->category = $categories;
            $user->contenttype = $contentTypes;
            
            $userList[] = $user;
        }
        
        return $userList;
    }
    
    public function getUsersByProfile($profile, $filters = [])
    {
        $filters['profile'] = $profile;
        return $this->getUsers($filters);
    }
    
    /**
     * Récupère un utilisateur par son ID avec toutes ses relations
     * 
     * @param string $id ID de l'utilisateur
     * @return \App\Models\User|null L'utilisateur ou null si non trouvé
     */
    public function getUserById($id)
    {
        $user = User::with([
            'country',
            'locality',
            'study',
            'language',
            'occupation',
            'categories',
            'contentTypes',
            'roles'
        ])->find($id);
        
        if (!$user) {
            return null;
        }
        
        // Convertir l'objet Eloquent en objet standard pour la vue
        $userData = (object) $user->toArray();
        
        // Ajouter les propriétés calculées
        $userData->category = $user->categories->pluck('name')
            ->map(function ($name) {
                return Str::limit($name, 10);
            })->implode(', ');
            
        $userData->contenttype = $user->contentTypes->pluck('name')->implode(', ');
        $userData->profiles = $user->roles->pluck('typerole')->implode(', ');
        
        // Ajout des noms des relations pour compatibilité avec l'ancienne méthode
        $userData->country = optional($user->country)->name;
        $userData->locality = optional($user->locality)->name;
        $userData->study = optional($user->study)->name;
        $userData->lang = optional($user->language)->name;
        $userData->profession = optional($user->occupation)->name;
        
        return $userData;
    }
    
    public function updateUser($id, $userData)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de la mise à jour de l\'utilisateur'
        ];
        
        try {
            $user = User::find($id);
            
            if (!$user) {
                $result['message'] = 'Utilisateur non trouvé';
                return $result;
            }
            
            $updateData = [];
            
            // Mettre à jour uniquement les champs fournis
            if (!empty($userData['firstname'])) $updateData['firstname'] = $userData['firstname'];
            if (!empty($userData['lastname'])) $updateData['lastname'] = $userData['lastname'];
            if (!empty($userData['country_id'])) $updateData['country_id'] = $userData['country_id'];
            if (!empty($userData['locality_id'])) $updateData['locality_id'] = $userData['locality_id'];
            if (!empty($userData['lang_id'])) $updateData['lang_id'] = $userData['lang_id'];
            if (!empty($userData['study_id'])) $updateData['study_id'] = $userData['study_id'];
            if (!empty($userData['occupation_id'])) $updateData['occupation_id'] = $userData['occupation_id'];
            if (!empty($userData['occupation'])) $updateData['occupation'] = $userData['occupation'];
            if (!empty($userData['phone'])) $updateData['phone'] = $userData['phone'];
            if (!empty($userData['vuesmoyen'])) $updateData['vuesmoyen'] = $userData['vuesmoyen'];
            
            if (!empty($userData['password'])) {
                $updateData['password'] = Hash::make($userData['password']);
            }
            
            if (count($updateData) > 0) {
                User::where('id', $id)->update($updateData);
            }
            
            // Mise à jour des catégories et types de contenu si nécessaire
            $processingTransaction = false;
            
            if (!empty($userData['categories']) || !empty($userData['contentTypes'])) {
                DB::beginTransaction();
                $processingTransaction = true;
                
                if (!empty($userData['categories'])) {
                    // Utilisation de sync au lieu de delete + insert
                    $user->categories()->sync($userData['categories']);
                }
                
                if (!empty($userData['contentTypes'])) {
                    // Utilisation de sync au lieu de delete + insert
                    $user->contentTypes()->sync($userData['contentTypes']);
                }
                
                DB::commit();
                $processingTransaction = false;
            }
            
            $result['success'] = true;
            $result['message'] = 'Utilisateur mis à jour avec succès';
            
        } catch (\Exception $e) {
            if (isset($processingTransaction) && $processingTransaction) {
                DB::rollBack();
            }
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    public function toggleUserStatus($id)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors du changement de statut',
            'new_status' => null
        ];
        
        try {
            $user = User::find($id);
            
            if (!$user) {
                $result['message'] = 'Utilisateur non trouvé';
                return $result;
            }
            
            $newStatus = !$user->enabled;
            
            User::where('id', $id)->update([
                'enabled' => $newStatus
            ]);
            
            $result['success'] = true;
            $result['message'] = 'Statut modifié avec succès';
            $result['new_status'] = $newStatus;
            
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    public function getInfluencersByCategory($categoryId)
    {
        return User::select('users.*', 'countries.name as country', 'localities.name as locality')
            ->leftJoin('countries', 'countries.id', '=', 'users.country_id')
            ->leftJoin('localities', 'localities.id', '=', 'users.locality_id')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->join('category_user', function($join) use ($categoryId) {
                $join->on('category_user.user_id', '=', 'users.id')
                     ->where('category_user.category_id', '=', $categoryId);
            })
            ->where('roles.typerole', '=', 'DIFFUSEUR')
            ->where('users.enabled', '=', true)
            ->orderByDesc('users.vuesmoyen')
            ->get()
            ->map(function ($user) {
                // Ajouter les catégories agrégées
                $categories = DB::table('category_user')
                    ->join('categories', 'categories.id', '=', 'category_user.category_id')
                    ->where('category_user.user_id', $user->id)
                    ->pluck('categories.name')
                    ->map(function ($name) {
                        return Str::limit($name, 10);
                    })
                    ->implode(', ');
                
                $user->categories = $categories;
                return $user;
            });
    }
    
    public function getUserStats()
    {
        // Requêtes Eloquent pour les statistiques des utilisateurs
        $totalUsers = User::count();
        $activeUsers = User::where('enabled', true)->count();
        $inactiveUsers = User::where('enabled', false)->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        $notVerifiedUsers = User::whereNull('email_verified_at')->count();
        $totalVuesmoyen = User::sum('vuesmoyen');
        
        // Requêtes pour les utilisateurs par type
        $adminRole = Role::where('typerole', 'ADMIN')->first();
        $announcerRole = Role::where('typerole', 'ANNONCEUR')->first();
        $influencerRole = Role::where('typerole', 'DIFFUSEUR')->first();
        
        $admins = 0;
        $announcers = 0;
        $influencers = 0;
        
        if ($adminRole) {
            $admins = DB::table('role_user')
                ->where('role_id', $adminRole->id)
                ->count();
        }
        
        if ($announcerRole) {
            $announcers = DB::table('role_user')
                ->where('role_id', $announcerRole->id)
                ->count();
        }
        
        if ($influencerRole) {
            $influencers = DB::table('role_user')
                ->where('role_id', $influencerRole->id)
                ->count();
        }
        
        return [
            'total' => $totalUsers,
            'active' => $activeUsers,
            'inactive' => $inactiveUsers,
            'verified' => $verifiedUsers,
            'not_verified' => $notVerifiedUsers,
            'admins' => $admins,
            'announcers' => $announcers,
            'influencers' => $influencers,
            'total_vuesmoyen' => $totalVuesmoyen,
        ];
    }
    
    public function getRecentUsers($limit = 5)
    {
        return User::select('users.*')
            ->orderByDesc('users.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                // Récupérer les profils de l'utilisateur
                $profiles = DB::table('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->where('role_user.user_id', $user->id)
                    ->pluck('roles.typerole')
                    ->implode(', ');
                
                $user->profiles = $profiles;
                return $user;
            });
    }
    
    /**
     * Récupère les diffuseurs recommandés pour un annonceur
     * 
     * @param string $clientId ID de l'annonceur
     * @param int $limit Nombre de diffuseurs à recommander
     * @return array Liste des diffuseurs recommandés
     */
    public function getRecommendedAgents($clientId, $limit = 4)
    {
        try {
            // Au lieu de dépendre d'une structure spécifique de la table category_task,
            // récupérons simplement les diffuseurs les plus populaires
            return $this->getPopularAgentsWithEloquent($limit);
        } catch (Exception $e) {
            // En cas d'erreur, retourner un tableau vide
            return [];
        }
    }
    
    /**
     * Récupère les diffuseurs les plus populaires avec Eloquent
     * 
     * @param int $limit Nombre de diffuseurs à retourner
     * @return array Liste des diffuseurs populaires
     */
    public function getPopularAgentsWithEloquent($limit = 4)
    {
        $influencerRole = Role::where('typerole', 'DIFFUSEUR')->first();
        
        if (!$influencerRole) {
            return [];
        }
        
        return User::select(
                'users.id', 
                'users.firstname', 
                'users.lastname', 
                'users.email',
                'users.phone', 
                'users.vuesmoyen',
                'users.enabled',
                DB::raw('coalesce(AVG(assignments.vues), 0) as avg_vues'),
                'countries.name as country', 
                'localities.name as locality'
            )
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->leftJoin('assignments', 'users.id', '=', 'assignments.agent_id')
            ->leftJoin('countries', 'users.country_id', '=', 'countries.id')
            ->leftJoin('localities', 'users.locality_id', '=', 'localities.id')
            ->where('role_user.role_id', $influencerRole->id)
            ->where('users.enabled', true)
            ->groupBy(
                'users.id', 
                'users.firstname', 
                'users.lastname', 
                'users.email',
                'users.phone', 
                'users.vuesmoyen',
                'users.enabled',
                'countries.name', 
                'localities.name'
            )
            ->orderByDesc(DB::raw('coalesce(AVG(assignments.vues), 0)'))
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                // Ajouter les catégories
                $categories = DB::table('category_user')
                    ->join('categories', 'categories.id', '=', 'category_user.category_id')
                    ->where('category_user.user_id', $user->id)
                    ->pluck('categories.name')
                    ->implode(', ');
                
                $user->category = $categories;
                return $user;
            });
    }
    
    /**
     * Récupère les diffuseurs les plus populaires (méthode obsolète, utilisez getPopularAgentsWithEloquent)
     * 
     * @param int $limit Nombre de diffuseurs à retourner
     * @return array Liste des diffuseurs populaires
     */
    public function getPopularAgents($limit = 4)
    {
        return $this->getPopularAgentsWithEloquent($limit);
    }
    
    /**
     * Récupère les IDs des catégories d'un utilisateur
     * 
     * @param string $userId ID de l'utilisateur
     * @return array Liste des IDs des catégories
     */
    public function getUserCategories($userId)
    {
        return DB::table('category_user')
            ->where('user_id', $userId)
            ->pluck('category_id')
            ->toArray();
    }
    
    /**
     * Récupère les statistiques d'affectation pour un utilisateur
     * 
     * @param string $userId ID de l'utilisateur
     * @return array Statistiques d'affectation
     */
    public function getAssignmentStats($userId)
    {
        // Total des affectations
        $totalCount = Assignment::where('agent_id', $userId)->count();
        
        // Affectations terminées
        $completedCount = Assignment::where('agent_id', $userId)
            ->where('status', 'COMPLETED')
            ->count();
        
        // Affectations en attente
        $pendingCount = Assignment::where('agent_id', $userId)
            ->where('status', 'PENDING')
            ->count();
        
        // Total des vues
        $totalVues = Assignment::where('agent_id', $userId)
            ->where('status', 'COMPLETED')
            ->sum('vues');
        
        // Total des gains
        $totalGain = Assignment::where('agent_id', $userId)
            ->where('status', 'COMPLETED')
            ->sum('gain');
        
        return [
            'total_count' => $totalCount,
            'completed_count' => $completedCount,
            'pending_count' => $pendingCount,
            'total_vues' => $totalVues ?? 0,
            'total_gain' => $totalGain ?? 0,
        ];
    }
}