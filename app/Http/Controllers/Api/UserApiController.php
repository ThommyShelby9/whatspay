<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class UserApiController extends Controller
{
    use Utils;
    
    protected $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    public function index(Request $request)
    {
        try {
            // Filtres optionnels
            $filters = [
                'profile' => $request->profile,
                'country_id' => $request->country_id,
                'locality_id' => $request->locality_id,
                'category_id' => $request->category_id,
            ];
            
            $users = $this->userService->getUsers($filters);
            
            return response()->json([
                'error' => false,
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }
            
            return response()->json([
                'error' => false,
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }
            
            $userData = $request->all();
            $result = $this->userService->updateUser($id, $userData);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Utilisateur mis à jour avec succès'
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function toggleStatus($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return response()->json([
                    'error' => true,
                    'message' => 'Utilisateur non trouvé'
                ], 404);
            }
            
            $result = $this->userService->toggleUserStatus($id);
            
            if ($result['success']) {
                return response()->json([
                    'error' => false,
                    'message' => 'Statut de l\'utilisateur modifié avec succès',
                    'new_status' => $result['new_status']
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors du changement de statut: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getInfluencersByCategory($categoryId)
    {
        try {
            $influencers = $this->userService->getInfluencersByCategory($categoryId);
            
            return response()->json([
                'error' => false,
                'influencers' => $influencers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des diffuseurs: ' . $e->getMessage()
            ], 500);
        }
    }
}