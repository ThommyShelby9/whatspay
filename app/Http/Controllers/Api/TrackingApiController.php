<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Link;
use App\Services\TrackingService;
use App\Traits\Utils;
use Illuminate\Http\Request;

class TrackingApiController extends Controller
{
    use Utils;

    protected $trackingService;

    public function __construct(TrackingService $trackingService)
    {
        $this->trackingService = $trackingService;
    }

    public function trackClick(string $linkId, Request $request)
    {
        try {
            // Vérifier que le lien existe
            $link = Link::find($linkId);

            if (!$link) {
                return abort(404, "Link not found");
            }

            // Collecter les données sur le clic
            $clickData = [
                'link_id' => $linkId,
                'user_agent' => $request->header('User-Agent'),
                'ip' => $request->ip(),
                'referer' => $request->header('Referer'),
                'timestamp' => gmdate('Y-m-d H:i:s'),
                'device' => $this->detectDevice($request->header('User-Agent')),
                'geo_data' => $this->getGeoData($request->ip()),
            ];

            // Enregistrer le clic
            $this->trackingService->recordClick($clickData);

            // Récupérer l'URL de destination
            $redirectUrl = $this->trackingService->getRedirectUrl($linkId);

            return redirect()->away($redirectUrl);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de l\'enregistrement du clic: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStats(Request $request, $taskId)
    {
        try {
            $stats = $this->trackingService->getTaskStatistics($taskId);

            return response()->json([
                'error' => false,
                'stats' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getGlobalStats(Request $request)
    {
        try {
            $filters = [
                'client_id' => $request->client_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ];

            $stats = $this->trackingService->getGlobalStatistics($filters);

            return response()->json([
                'error' => false,
                'stats' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Erreur lors de la récupération des statistiques globales: ' . $e->getMessage()
            ], 500);
        }
    }

    private function detectDevice($userAgent)
    {
        // Logique simplifiée de détection de l'appareil
        if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) {
            return 'mobile';
        } elseif (strpos($userAgent, 'Tablet') !== false || strpos($userAgent, 'iPad') !== false) {
            return 'tablet';
        } else {
            return 'desktop';
        }
    }

    private function getGeoData($ip)
    {
        // Cette fonction devrait utiliser un service de géolocalisation IP
        // Pour l'instant, retournons des données fictives
        return [
            'country' => 'Bénin',
            'city' => 'Porto-Novo',
            'region' => 'Ouémé',
        ];
    }
}
