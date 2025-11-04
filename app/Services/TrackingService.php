<?php

namespace App\Services;

use App\Models\Link;
use App\Models\Linkcall;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;

class TrackingService
{
    use Utils;
    
    /**
     * Génère un lien de suivi pour une URL
     * 
     * @param string $originalUrl URL originale
     * @param string $taskId ID de la tâche/campagne
     * @return array Informations sur le lien de suivi
     */
    public function generateTrackingLink($originalUrl, $taskId)
    {
        $linkId = $this->getId();
        $link = Link::create([
            'id' => $linkId,
            'url' => $originalUrl,
            'task_id' => $taskId
        ]);
        
        // Générer l'URL de tracking (format: domaine/track/{linkId})
        $trackingUrl = config('app.url') . '/track/' . $linkId;
        
        return [
            'link_id' => $linkId,
            'original_url' => $originalUrl,
            'tracking_url' => $trackingUrl
        ];
    }
    
    /**
     * Enregistre un clic sur un lien de suivi
     * 
     * @param array $clickData Données du clic
     * @return array Résultat de l'opération
     */
    public function recordClick($clickData)
    {
        $result = [
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'enregistrement du clic'
        ];
        
        try {
            // Vérifier que le lien existe
            $link = Link::find($clickData['link_id']);
            
            if (!$link) {
                $result['message'] = 'Lien non trouvé';
                return $result;
            }
            
            // Créer un enregistrement de clic
            $linkCallId = $this->getId();
            $linkCall = Linkcall::create([
                'id' => $linkCallId,
                'calldetails' => json_encode($clickData),
                'link_id' => $clickData['link_id']
            ]);
            
            $result['success'] = true;
            $result['message'] = 'Clic enregistré avec succès';
            
        } catch (\Exception $e) {
            $result['message'] = 'Erreur: ' . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Récupère l'URL de redirection pour un lien de suivi
     * 
     * @param string $linkId ID du lien
     * @return string|null URL de redirection
     */
    public function getRedirectUrl($linkId)
    {
        $link = Link::find($linkId);
        
        if ($link) {
            return $link->url;
        }
        
        return null;
    }
    
    /**
     * Récupère les statistiques d'une tâche/campagne
     * 
     * @param string $taskId ID de la tâche
     * @return array Statistiques de la tâche
     */
    public function getTaskStatistics($taskId)
    {
        // Récupérer les liens associés à la tâche
        $links = DB::select("select id from links where task_id = :task_id", [
            'task_id' => $taskId
        ]);
        
        $linkIds = [];
        foreach ($links as $link) {
            $linkIds[] = $link->id;
        }
        
        if (count($linkIds) == 0) {
            return [
                'total_clicks' => 0,
                'unique_clicks' => 0,
                'click_rate' => 0,
                'devices' => [
                    'desktop' => 0,
                    'mobile' => 0,
                    'tablet' => 0,
                    'unknown' => 0
                ]
            ];
        }
        
        // Formatage des IDs pour la requête SQL
        $linkIdsStr = "'" . implode("','", $linkIds) . "'";
        
        // Statistiques générales
        $totalClicks = DB::select("select count(*) as count from linkcalls where link_id in ($linkIdsStr)")[0]->count;
        
        // Clics uniques (par IP)
        $uniqueClicks = DB::select("select count(distinct(json_extract_path_text(calldetails::json, 'ip'))) as count from linkcalls where link_id in ($linkIdsStr)")[0]->count;
        
        // Répartition par appareil
        $deviceStats = [
            'desktop' => DB::select("select count(*) as count from linkcalls where link_id in ($linkIdsStr) and json_extract_path_text(calldetails::json, 'device') = 'desktop'")[0]->count,
            'mobile' => DB::select("select count(*) as count from linkcalls where link_id in ($linkIdsStr) and json_extract_path_text(calldetails::json, 'device') = 'mobile'")[0]->count,
            'tablet' => DB::select("select count(*) as count from linkcalls where link_id in ($linkIdsStr) and json_extract_path_text(calldetails::json, 'device') = 'tablet'")[0]->count,
            'unknown' => DB::select("select count(*) as count from linkcalls where link_id in ($linkIdsStr) and (json_extract_path_text(calldetails::json, 'device') is null or json_extract_path_text(calldetails::json, 'device') not in ('desktop', 'mobile', 'tablet'))")[0]->count,
        ];
        
        // Récupérer le nombre total d'impressions (vues) de la tâche
        $totalViews = DB::select("select sum(vues) as total from assignments where task_id = :task_id", [
            'task_id' => $taskId
        ])[0]->total ?: 0;
        
        // Calculer le taux de clic (CTR)
        $clickRate = $totalViews > 0 ? ($uniqueClicks / $totalViews) * 100 : 0;
        
        return [
            'total_clicks' => $totalClicks,
            'unique_clicks' => $uniqueClicks,
            'total_views' => $totalViews,
            'click_rate' => round($clickRate, 2),
            'devices' => $deviceStats
        ];
    }
    
    /**
     * Récupère les statistiques globales des campagnes
     * 
     * @param array $filters Filtres à appliquer
     * @return array Statistiques globales
     */
    public function getGlobalStatistics($filters = [])
    {
        $query = "select 
            tasks.id as task_id,
            tasks.name as task_name,
            count(distinct linkcalls.id) as total_clicks,
            count(distinct json_extract_path_text(calldetails::json, 'ip')) as unique_clicks,
            sum(assignments.vues) as total_views
            from tasks
            left join links on tasks.id = links.task_id
            left join linkcalls on links.id = linkcalls.link_id
            left join assignments on tasks.id = assignments.task_id
            where 1=1";
            
        $params = [];
        
        if (!empty($filters['client_id'])) {
            $query .= " and tasks.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (!empty($filters['category_id'])) {
            $query .= " and tasks.id in (select task_id from category_task where category_id = :category_id)";
            $params['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " and tasks.startdate >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " and tasks.enddate <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $query .= " group by tasks.id, tasks.name
            order by total_clicks desc";
            
        $stats = DB::select($query, $params);
        
        // Calculer le CTR pour chaque tâche
        foreach ($stats as &$stat) {
            $stat->click_rate = $stat->total_views > 0 ? round(($stat->unique_clicks / $stat->total_views) * 100, 2) : 0;
        }
        
        return $stats;
    }
    
    /**
     * Récupère les campagnes les plus performantes
     * 
     * @param int $limit Nombre de campagnes à récupérer
     * @return array Campagnes les plus performantes
     */
    public function getTopPerformingCampaigns($limit = 5)
    {
        $stats = $this->getGlobalStatistics();
        
        // Trier par taux de clics
        usort($stats, function($a, $b) {
            return $b->click_rate <=> $a->click_rate;
        });
        
        return array_slice($stats, 0, $limit);
    }
    
    /**
     * Récupère les statistiques de performance par catégorie
     * 
     * @param array $filters Filtres à appliquer
     * @return array Performance par catégorie
     */
    public function getPerformanceByCategory($filters = [])
    {
        $query = "select 
            c.id as category_id,
            c.name as category_name,
            count(distinct t.id) as campaign_count,
            sum(a.vues) as total_views,
            count(distinct lc.id) as total_clicks
            from categories c
            left join category_task ct on c.id = ct.category_id
            left join tasks t on ct.task_id = t.id
            left join assignments a on t.id = a.task_id
            left join links l on t.id = l.task_id
            left join linkcalls lc on l.id = lc.link_id
            where 1=1";
            
        $params = [];
        
        if (!empty($filters['client_id'])) {
            $query .= " and t.client_id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " and t.startdate >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " and t.enddate <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $query .= " group by c.id, c.name
            order by total_clicks desc";
            
        $stats = DB::select($query, $params);
        
        // Calculer le CTR pour chaque catégorie
        foreach ($stats as &$stat) {
            $stat->click_rate = $stat->total_views > 0 ? round(($stat->total_clicks / $stat->total_views) * 100, 2) : 0;
        }
        
        return $stats;
    }
    
    /**
     * Récupère les statistiques de performance par annonceur
     * 
     * @param array $filters Filtres à appliquer
     * @return array Performance par annonceur
     */
    public function getPerformanceByClient($filters = [])
    {
        $query = "select 
            u.id as client_id,
            concat(u.firstname, ' ', u.lastname) as client_name,
            count(distinct t.id) as campaign_count,
            sum(a.vues) as total_views,
            count(distinct lc.id) as total_clicks
            from users u
            left join tasks t on u.id = t.client_id
            left join assignments a on t.id = a.task_id
            left join links l on t.id = l.task_id
            left join linkcalls lc on l.id = lc.link_id
            where 1=1";
            
        $params = [];
        
        if (!empty($filters['client_id'])) {
            $query .= " and u.id = :client_id";
            $params['client_id'] = $filters['client_id'];
        }
        
        if (!empty($filters['category_id'])) {
            $query .= " and t.id in (select task_id from category_task where category_id = :category_id)";
            $params['category_id'] = $filters['category_id'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " and t.startdate >= :start_date";
            $params['start_date'] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " and t.enddate <= :end_date";
            $params['end_date'] = $filters['end_date'];
        }
        
        $query .= " group by u.id, u.firstname, u.lastname
            order by total_clicks desc";
            
        $stats = DB::select($query, $params);
        
        // Calculer le CTR pour chaque annonceur
        foreach ($stats as &$stat) {
            $stat->click_rate = $stat->total_views > 0 ? round(($stat->total_clicks / $stat->total_views) * 100, 2) : 0;
        }
        
        return $stats;
    }
    
    /**
     * Récupère la distribution géographique des clics
     * 
     * @return array Distribution géographique
     */
    public function getGeographicDistribution()
    {
        $query = "select 
            json_extract_path_text(calldetails::json, 'geo_data', 'country') as country,
            count(*) as click_count
            from linkcalls
            where json_extract_path_text(calldetails::json, 'geo_data', 'country') is not null
            group by country
            order by click_count desc";
            
        return DB::select($query);
    }
}