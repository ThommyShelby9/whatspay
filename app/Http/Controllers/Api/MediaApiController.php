<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class MediaApiController extends Controller
{
    use Utils;

    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function upload(Request $request)
    {
        try {
            // Validation
            $request->validate([
                'files' => 'required',
                'files.*' => 'file|max:10240', // 10MB max par fichier
                'user_id' => 'nullable|string',
                'task_id' => 'nullable|string'
            ]);
            
            // Vérification si c'est un seul fichier ou plusieurs
            $files = $request->file('files');
            
            if (!$files) {
                return response()->json([
                    'error' => true,
                    'message' => 'Aucun fichier trouvé dans la requête.'
                ], 400);
            }
            
            if (!is_array($files)) {
                $files = [$files];
            }
            
            $userId = $request->input('user_id');
            $taskId = $request->input('task_id');
            
            $result = $this->mediaService->uploadFiles($files, $userId, $taskId);
            
            // Transformer les résultats pour l'API
            $mediaList = [];
            foreach ($result as $media) {
                $mediaList[] = [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'original_name' => $media->original_name,
                    'file_type' => $media->file_type,
                    'url' => asset($media->path . '/' . $media->file_name)
                ];
            }
            
            return response()->json([
                'error' => false,
                'files' => $mediaList,
                'message' => (count($result) > 1 ? 'Fichiers enregistrés avec succès.' : 'Fichier enregistré avec succès.')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de l\'enregistrement du fichier : ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMedia($id)
    {
        try {
            $media = $this->mediaService->getMedia($id);
            
            if (!$media) {
                return response()->json([
                    'error' => true,
                    'message' => 'Fichier non trouvé.'
                ], 404);
            }
            
            return response()->json([
                'error' => false,
                'media' => [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'original_name' => $media->original_name,
                    'file_type' => $media->file_type,
                    'url' => $media->full_url,
                    'created_at' => $media->created_at
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération du fichier : ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteMedia($id)
    {
        try {
            $result = $this->mediaService->deleteMedia($id);
            
            if ($result) {
                return response()->json([
                    'error' => false,
                    'message' => 'Fichier supprimé avec succès.'
                ], 200);
            } else {
                return response()->json([
                    'error' => true,
                    'message' => 'Fichier non trouvé.'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la suppression du fichier : ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getTaskMedia($taskId)
    {
        try {
            $mediaList = $this->mediaService->getTaskMedia($taskId);
            
            $result = $mediaList->map(function($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'original_name' => $media->original_name,
                    'file_type' => $media->file_type,
                    'url' => $media->full_url,
                    'created_at' => $media->created_at
                ];
            });
            
            return response()->json([
                'error' => false,
                'media' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des fichiers : ' . $e->getMessage()
            ], 500);
        }
    }
}