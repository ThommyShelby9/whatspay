<?php

namespace App\Services;

use App\Models\Category;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    use Utils;
    
    /**
     * Récupère toutes les catégories
     * 
     * @return array Liste des catégories
     */
    public function getAllCategories()
    {
        return DB::select("SELECT * FROM categories ORDER BY name ASC");
    }
    
    /**
     * Récupère une catégorie par son ID
     * 
     * @param string $id ID de la catégorie
     * @return object|null La catégorie ou null si non trouvée
     */
    public function getCategoryById($id)
    {
        $categories = DB::select("SELECT * FROM categories WHERE id = ?", [$id]);
        return count($categories) > 0 ? $categories[0] : null;
    }
    
    /**
     * Récupère les catégories associées à une tâche
     * 
     * @param string $taskId ID de la tâche
     * @return array Liste des catégories associées à la tâche
     */
    public function getCategoriesByTask($taskId)
    {
        return DB::select("
            SELECT c.*
            FROM categories c
            JOIN category_task ct ON c.id = ct.category_id
            WHERE ct.task_id = ?
            ORDER BY c.name ASC
        ", [$taskId]);
    }
}