<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Link;
use App\Models\Linkcall;
use App\Traits\Utils;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        // Récupérer les IDs de liens liés à la tâche
        $linkIds = Link::where('task_id', $taskId)->pluck('id')->toArray();

        if (empty($linkIds)) {
            return [
                'total_clicks' => 0,
                'unique_clicks' => 0,
                'click_rate' => 0,
                'total_views' => 0,
                'devices' => [
                    'desktop' => 0,
                    'mobile' => 0,
                    'tablet' => 0,
                    'unknown' => 0,
                ],
            ];
        }

        // Total clicks
        $totalClicks = Linkcall::whereIn('link_id', $linkIds)->count();

        // Unique clicks (distinct IPs extracted from JSON calldetails)
        $uniqueClicks = Linkcall::whereIn('link_id', $linkIds)
            ->distinct()
            ->count(DB::raw("(calldetails::jsonb->>'ip')"));

        // Device breakdown (Postgres JSON extraction)
        $deviceStats = [
            'desktop' => Linkcall::whereIn('link_id', $linkIds)
                ->whereRaw("(calldetails::jsonb->>'device') = 'desktop'")->count(),
            'mobile' => Linkcall::whereIn('link_id', $linkIds)
                ->whereRaw("(calldetails::jsonb->>'device') = 'mobile'")->count(),
            'tablet' => Linkcall::whereIn('link_id', $linkIds)
                ->whereRaw("(calldetails::jsonb->>'device') = 'tablet'")->count(),
            'unknown' => Linkcall::whereIn('link_id', $linkIds)
                ->where(function ($q) {
                    $q->whereNull(DB::raw("(calldetails::jsonb->>'device')"))
                        ->orWhereRaw("(calldetails::jsonb->>'device') NOT IN ('desktop','mobile','tablet')");
                })->count(),
        ];

        // Geographic distribution (countries)
        $geoData = Linkcall::whereIn('link_id', $linkIds)
            ->select(
                DB::raw("(calldetails::jsonb->'geo_data'->>'country') AS country"),
                DB::raw("(calldetails::jsonb->'geo_data'->>'region') AS region"),
                DB::raw("(calldetails::jsonb->'geo_data'->>'city') AS city"),
                DB::raw("COUNT(*) AS total")
            )
            ->groupBy(
                DB::raw("(calldetails::jsonb->'geo_data'->>'country')"),
                DB::raw("(calldetails::jsonb->'geo_data'->>'region')"),
                DB::raw("(calldetails::jsonb->'geo_data'->>'city')")
            )
            ->get()
            ->map(function ($row) {
                return [
                    'country' => $row->country ?? 'Inconnu',
                    'region'  => $row->region ?? 'Inconnu',
                    'city'    => $row->city ?? 'Inconnu',
                    'total'   => (int) $row->total,
                ];
            })
            ->toArray();

        // Total views (assignments.vues)
        $totalViews = (int) Assignment::where('task_id', $taskId)->sum('vues');

        // CTR (unique clicks / total views) * 100
        $clickRate = $totalViews > 0 ? round(($uniqueClicks / $totalViews) * 100, 2) : 0;

        // 1) Définir la plage des 7 derniers jours (incluant aujourd'hui)
        $end = Carbon::now();
        $start = (clone $end)->startOfDay()->subDays(6); // 7 jours: start..end

        // 2) Récupérer les agrégats par date (vues + clics) sur la plage
        $dailyRaw = Linkcall::whereIn('link_id', $linkIds)
            ->whereBetween('created_at', [$start, $end->endOfDay()])
            ->selectRaw("DATE(created_at) AS day_date")
            ->selectRaw("COUNT(*) AS views")
            ->selectRaw("COUNT(*) FILTER (WHERE (calldetails::jsonb->>'clicked') = 'true') AS clicks")
            ->groupBy('day_date')
            ->orderBy('day_date')
            ->get()
            ->keyBy(function ($row) {
                return $row->day_date; // 'YYYY-MM-DD'
            });

        // 3) Construire les tableaux complets (labels en français)
        $dailyDates = [];
        $dailyViews = [];
        $dailyClicks = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            // libellé court FR avec point, ex: "sam."
            $label = $date->locale('fr')->isoFormat('ddd'); // isoFormat('ddd') => "sam."
            $dayKey = $date->toDateString(); // 'YYYY-MM-DD'

            $dailyDates[] = $label;
            if (isset($dailyRaw[$dayKey])) {
                $dailyViews[] = (int) $dailyRaw[$dayKey]->views;
                $dailyClicks[] = (int) $dailyRaw[$dayKey]->clicks;
            } else {
                $dailyViews[] = 0;
                $dailyClicks[] = 0;
            }
        }

        $dailyStats = [
            'dates' => $dailyDates,
            'views' => $dailyViews,
            'clicks' => $dailyClicks,
        ];

        //Statistiques de la semaine
        $weekdayRaw = Linkcall::whereIn('link_id', $linkIds)
            ->selectRaw("EXTRACT(DOW FROM created_at)::int AS dow")
            ->selectRaw("COUNT(*) AS total_clicks")
            ->groupBy('dow')
            ->get()
            ->pluck('total_clicks', 'dow') // collection: key = dow, value = total_clicks
            ->toArray();

        // Mapping français (we want Monday first)
        $dowMap = [
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mer',
            4 => 'Jeu',
            5 => 'Ven',
            6 => 'Sam',
            0 => 'Dim'
        ];

        // Construire le tableau final en ordre Lun->Dim
        $weekdayData = [];
        foreach ($dowMap as $dow => $label) {
            $weekdayData[$label] = isset($weekdayRaw[$dow]) ? (int) $weekdayRaw[$dow] : 0;
        }

        return [
            'total_clicks' => (int) $totalClicks,
            'unique_clicks' => (int) $uniqueClicks,
            'total_views' => $totalViews,
            'click_rate' => $clickRate,
            'devices' => $deviceStats,
            'geography' => $geoData,
            'daily_data' => $dailyStats,
            'weekday_data' => $weekdayData
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
        $query = DB::table('tasks')
            ->leftJoin('links', 'tasks.id', '=', 'links.task_id')
            ->leftJoin('linkcalls', 'links.id', '=', 'linkcalls.link_id')
            ->leftJoin('assignments', 'tasks.id', '=', 'assignments.task_id')
            ->selectRaw("
            tasks.id as task_id,
            tasks.name as task_name,
            count(distinct linkcalls.id) as total_clicks,
            count(distinct (linkcalls.calldetails::jsonb->>'ip')) as unique_clicks,
            coalesce(sum(assignments.vues),0) as total_views
        ")
            ->groupBy('tasks.id', 'tasks.name')
            ->orderByDesc('total_clicks');

        if (!empty($filters['client_id'])) {
            $query->where('tasks.client_id', $filters['client_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->whereIn('tasks.id', function ($sub) use ($filters) {
                $sub->select('task_id')->from('category_task')->where('category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->where('tasks.startdate', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('tasks.enddate', '<=', $filters['end_date']);
        }

        $stats = $query->get()->map(function ($stat) {
            $stat->total_clicks = (int) $stat->total_clicks;
            $stat->unique_clicks = (int) $stat->unique_clicks;
            $stat->total_views = (int) $stat->total_views;
            $stat->click_rate = $stat->total_views > 0 ? round(($stat->unique_clicks / $stat->total_views) * 100, 2) : 0;
            return $stat;
        })->toArray();

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

        // Trier par click_rate décroissant
        usort($stats, function ($a, $b) {
            return ($b->click_rate ?? 0) <=> ($a->click_rate ?? 0);
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
        $query = DB::table('categories as c')
            ->leftJoin('category_task as ct', 'c.id', '=', 'ct.category_id')
            ->leftJoin('tasks as t', 'ct.task_id', '=', 't.id')
            ->leftJoin('assignments as a', 't.id', '=', 'a.task_id')
            ->leftJoin('links as l', 't.id', '=', 'l.task_id')
            ->leftJoin('linkcalls as lc', 'l.id', '=', 'lc.link_id')
            ->selectRaw("
            c.id as category_id,
            c.name as category_name,
            count(distinct t.id) as campaign_count,
            coalesce(sum(a.vues),0) as total_views,
            count(distinct lc.id) as total_clicks
        ")
            ->groupBy('c.id', 'c.name')
            ->orderByDesc('total_clicks');

        if (!empty($filters['client_id'])) {
            $query->where('t.client_id', $filters['client_id']);
        }

        if (!empty($filters['start_date'])) {
            $query->where('t.startdate', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('t.enddate', '<=', $filters['end_date']);
        }

        $stats = $query->get()->map(function ($stat) {
            $stat->campaign_count = (int) $stat->campaign_count;
            $stat->total_views = (int) $stat->total_views;
            $stat->total_clicks = (int) $stat->total_clicks;
            $stat->click_rate = $stat->total_views > 0 ? round(($stat->total_clicks / $stat->total_views) * 100, 2) : 0;
            return $stat;
        })->toArray();

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
        $query = DB::table('users as u')
            ->leftJoin('tasks as t', 'u.id', '=', 't.client_id')
            ->leftJoin('assignments as a', 't.id', '=', 'a.task_id')
            ->leftJoin('links as l', 't.id', '=', 'l.task_id')
            ->leftJoin('linkcalls as lc', 'l.id', '=', 'lc.link_id')
            ->selectRaw("
            u.id as client_id,
            concat(u.firstname, ' ', u.lastname) as client_name,
            count(distinct t.id) as campaign_count,
            coalesce(sum(a.vues),0) as total_views,
            count(distinct lc.id) as total_clicks
        ")
            ->groupBy('u.id', 'u.firstname', 'u.lastname')
            ->orderByDesc('total_clicks');

        if (!empty($filters['client_id'])) {
            $query->where('u.id', $filters['client_id']);
        }

        if (!empty($filters['category_id'])) {
            $query->whereIn('t.id', function ($sub) use ($filters) {
                $sub->select('task_id')->from('category_task')->where('category_id', $filters['category_id']);
            });
        }

        if (!empty($filters['start_date'])) {
            $query->where('t.startdate', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('t.enddate', '<=', $filters['end_date']);
        }

        $stats = $query->get()->map(function ($stat) {
            $stat->campaign_count = (int) $stat->campaign_count;
            $stat->total_views = (int) $stat->total_views;
            $stat->total_clicks = (int) $stat->total_clicks;
            $stat->click_rate = $stat->total_views > 0 ? round(($stat->total_clicks / $stat->total_views) * 100, 2) : 0;
            return $stat;
        })->toArray();

        return $stats;
    }


    /**
     * Récupère la distribution géographique des clics
     * 
     * @return array Distribution géographique
     */
    public function getGeographicDistribution()
    {
        $rows = DB::table('linkcalls')
            ->selectRaw("
            (calldetails->'geo_data'->>'country') as country,
            count(*) as click_count
        ")
            ->whereRaw("calldetails->'geo_data'->>'country' IS NOT NULL")
            ->groupBy('country')
            ->orderByDesc('click_count')
            ->get();

        return $rows->map(function ($r) {
            $r->click_count = (int) $r->click_count;
            return $r;
        })->toArray();
    }
}
