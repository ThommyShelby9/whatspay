<?php

namespace App\Services;

use App\Models\Media;
use App\Traits\Utils;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MediaService
{
    use Utils;

    /**
     * Upload de fichiers multiples
     *
     * @param array $files Tableau des fichiers à uploader
     * @param string|null $userId ID de l'utilisateur qui upload (optionnel)
     * @param string|null $taskId ID de la tâche associée (optionnel)
     * @return array Tableau des médias créés
     */
    public function uploadFiles(array $files, $userId = null, $taskId = null)
    {
        $savedMedia = [];

        foreach ($files as $file) {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/images');
            
            // Créer le répertoire s'il n'existe pas
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            
            $file->move($destinationPath, $filename);
            
            // Créer l'entrée dans la base de données
            $media = Media::create([
                'id' => $this->getId(),
                'file_name' => $filename,
                'file_type' => $file->getClientMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'path' => 'uploads/images',
                'state' => 'active',
                'user_id' => $userId,
                'task_id' => $taskId
            ]);

            $savedMedia[] = $media;
        }

        return $savedMedia;
    }

    /**
     * Récupère un média par son ID
     *
     * @param string $id ID du média à récupérer
     * @return Media|null Le média trouvé ou null
     */
    public function getMedia($id)
    {
        $media = Media::find($id);
        
        if ($media) {
            // Ajouter l'URL complète du fichier
            $media->full_url = asset($media->path . '/' . $media->file_name);
        }
        
        return $media;
    }

    /**
     * Supprime un média par son ID
     *
     * @param string $id ID du média à supprimer
     * @return bool Succès de la suppression
     */
    public function deleteMedia($id)
    {
        $media = Media::find($id);
        
        if (!$media) {
            return false;
        }
        
        // Supprimer le fichier physique
        $filePath = public_path($media->path . '/' . $media->file_name);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
        
        // Supprimer l'entrée dans la base de données
        return $media->delete();
    }

    /**
     * Récupère tous les médias associés à une tâche
     *
     * @param string $taskId ID de la tâche
     * @return Collection Collection de médias
     */
    public function getTaskMedia($taskId)
    {
        return Media::where('task_id', $taskId)
            ->where('state', 'active')
            ->get()
            ->map(function($media) {
                $media->full_url = asset($media->path . '/' . $media->file_name);
                return $media;
            });
    }

    /**
     * Change l'état d'un média
     *
     * @param string $id ID du média
     * @param string $state Nouvel état ('active', 'inactive', 'deleted')
     * @return bool Succès de l'opération
     */
    public function changeMediaState($id, $state)
    {
        $media = Media::find($id);
        
        if (!$media) {
            return false;
        }
        
        $media->state = $state;
        return $media->save();
    }
}