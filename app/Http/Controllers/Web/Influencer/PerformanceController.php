<?php
// File: app/Http/Controllers/Web/Influencer/PerformanceController.php

namespace App\Http\Controllers\Web\Influencer;

use App\Http\Controllers\Controller;
use App\Services\AssignmentService;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PerformanceController extends Controller
{
    use Utils;
    
    protected $assignmentService;
    
    public function __construct(
        AssignmentService $assignmentService
    ) {
        $this->assignmentService = $assignmentService;
    }
    
    public function index(Request $request)
    {
        $viewData = [];
        $alert = [];
        $this->setAlert($request, $alert);
        
        $userId = $request->session()->get('userid');
        
        // Filtres de dates
        $startDate = $request->get('start_date') ? Carbon::parse($request->get('start_date')) : Carbon::now()->subMonth();
        $endDate = $request->get('end_date') ? Carbon::parse($request->get('end_date')) : Carbon::now();
        
        // Récupérer les statistiques de performance
        $viewData["assignmentStats"] = $this->assignmentService->getAgentAssignmentStats($userId);
        
        // Récupérer les missions de l'agent
        $assignments = $this->assignmentService->getAgentAssignments($userId);
        
        // Filtrer les missions complétées
        $completedAssignments = $assignments->filter(function($assignment) {
            return $assignment->status === 'COMPLETED';
        });
        
        // Préparer les données de performance pour le tableau
        $viewData["performanceData"] = $this->preparePerformanceData($completedAssignments);
        
        // Préparer les données pour les graphiques
        $viewData["viewsChartData"] = $this->prepareViewsChartData($completedAssignments);
        $viewData["categoryChartData"] = $this->prepareCategoryChartData($completedAssignments);
        
        $this->setViewData($request, $viewData);
        
        return view('influencer.performance.index', [
            'alert' => $alert,
            'viewData' => $viewData,
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Performances',
            'pagetilte' => 'Mes Performances',
            'pagecardtilte' => 'Statistiques et métriques'
        ]);
    }
    
    /**
     * Prépare les données de performance pour le tableau
     */
    private function preparePerformanceData($assignments)
    {
        $performanceData = [];
        
        foreach ($assignments as $assignment) {
            // Définir une valeur cible par défaut - Normalement cela devrait venir de la tâche
            $target = 1000; 
            
            // Calculer la performance (% de l'objectif atteint)
            $performance = $target > 0 ? min(($assignment->vues / $target) * 100, 200) : 0;
            
            $performanceData[] = [
                'campaign' => $assignment->task_name,
                'date' => $assignment->submission_date,
                'views' => $assignment->vues,
                'target' => $target,
                'performance' => round($performance),
                'rating' => $this->calculateRating($performance)
            ];
        }
        
        return $performanceData;
    }
    
    /**
     * Calcule l'évaluation (1-5 étoiles) basée sur la performance
     */
    private function calculateRating($performance)
    {
        if ($performance >= 120) return 5;
        if ($performance >= 100) return 4;
        if ($performance >= 80) return 3;
        if ($performance >= 60) return 2;
        return 1;
    }
    
    /**
     * Prépare les données pour le graphique d'évolution des vues
     */
    private function prepareViewsChartData($assignments)
    {
        // Données pour les différentes périodes
        return [
            'week' => $this->getWeeklyViewsData($assignments),
            'month' => $this->getMonthlyViewsData($assignments),
            'year' => $this->getYearlyViewsData($assignments)
        ];
    }
    
    /**
     * Récupère les données pour le graphique hebdomadaire
     */
    private function getWeeklyViewsData($assignments)
    {
        $weekData = array_fill(0, 7, 0); // Un élément par jour de la semaine
        
        // Filtrer les missions complétées dans la semaine en cours
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        
        foreach ($assignments as $assignment) {
            if (empty($assignment->submission_date)) continue;
            
            $submissionDate = Carbon::parse($assignment->submission_date);
            if ($submissionDate < $startOfWeek || $submissionDate > $endOfWeek) continue;
            
            $dayOfWeek = $submissionDate->dayOfWeekIso - 1; // 0 = lundi, 6 = dimanche
            $weekData[$dayOfWeek] += $assignment->vues;
        }
        
        return $weekData;
    }
    
    /**
     * Récupère les données pour le graphique mensuel
     */
    private function getMonthlyViewsData($assignments)
    {
        $monthData = array_fill(0, 4, 0); // Un élément par semaine
        
        // Filtrer les missions complétées dans le mois en cours
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        foreach ($assignments as $assignment) {
            if (empty($assignment->submission_date)) continue;
            
            $submissionDate = Carbon::parse($assignment->submission_date);
            if ($submissionDate < $startOfMonth || $submissionDate > $endOfMonth) continue;
            
            $weekOfMonth = floor(($submissionDate->day - 1) / 7); // 0-3
            if ($weekOfMonth >= count($monthData)) $weekOfMonth = count($monthData) - 1;
            $monthData[$weekOfMonth] += $assignment->vues;
        }
        
        return $monthData;
    }
    
    /**
     * Récupère les données pour le graphique annuel
     */
    private function getYearlyViewsData($assignments)
    {
        $yearData = array_fill(0, 12, 0); // Un élément par mois
        
        // Filtrer les missions complétées dans l'année en cours
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        
        foreach ($assignments as $assignment) {
            if (empty($assignment->submission_date)) continue;
            
            $submissionDate = Carbon::parse($assignment->submission_date);
            if ($submissionDate < $startOfYear || $submissionDate > $endOfYear) continue;
            
            $monthOfYear = $submissionDate->month - 1; // 0-11
            $yearData[$monthOfYear] += $assignment->vues;
        }
        
        return $yearData;
    }
    
    /**
     * Prépare les données pour le graphique par catégorie
     */
    private function prepareCategoryChartData($assignments)
    {
        // Grouper par nom de tâche comme approximation de catégorie
        $categories = [];
        
        foreach ($assignments as $assignment) {
            $taskName = $assignment->task_name ?? 'Autres';
            
            // Déterminer la catégorie basée sur le nom de la tâche (logique simplifiée)
            $category = 'Autres';
            $taskNameLower = strtolower($taskName);
            
            if (strpos($taskNameLower, 'mode') !== false || strpos($taskNameLower, 'fashion') !== false) {
                $category = 'Mode';
            } elseif (strpos($taskNameLower, 'tech') !== false || strpos($taskNameLower, 'digital') !== false) {
                $category = 'Technologie';
            } elseif (strpos($taskNameLower, 'food') !== false || strpos($taskNameLower, 'aliment') !== false) {
                $category = 'Alimentation';
            } elseif (strpos($taskNameLower, 'beauté') !== false || strpos($taskNameLower, 'beauty') !== false) {
                $category = 'Beauté';
            }
            
            if (!isset($categories[$category])) {
                $categories[$category] = 0;
            }
            
            $categories[$category] += $assignment->vues;
        }
        
        // Si nous n'avons pas assez de catégories, ajouter les manquantes avec 0
        $defaultCategories = ['Mode', 'Technologie', 'Alimentation', 'Beauté', 'Autres'];
        foreach ($defaultCategories as $category) {
            if (!isset($categories[$category])) {
                $categories[$category] = 0;
            }
        }
        
        // Calculer les pourcentages
        $total = array_sum($categories);
        $values = [];
        
        if ($total > 0) {
            foreach ($categories as $category => $views) {
                $values[] = round(($views / $total) * 100);
            }
        } else {
            // Valeurs par défaut si aucune donnée
            $values = [35, 25, 20, 15, 5];
        }
        
        return [
            'labels' => array_keys($categories),
            'values' => $values
        ];
    }
    
    private function setAlert(Request &$request, &$alert)
    {
        $alert = [
            'message' => (!empty($request->message) ? $request->message : (!empty(session('message')) ? session('message') : "")),
            'type' => (!empty($request->type) ? $request->type : (!empty(session('type')) ? session('type') : "success")),
        ];
    }

    private function setViewData(Request &$request, &$viewData)
    {
        $viewData['uri'] = \Route::currentRouteName();
        $viewData['baseUrl'] = config('app.url');
        $viewData['version'] = gmdate('YmdHis');
        $viewData['user'] = ($request->session()->has('user') ? $request->session()->get('user') : "");
        $viewData['userid'] = ($request->session()->has('userid') ? $request->session()->get('userid') : "");
        $viewData['userprofile'] = ($request->session()->has('userprofile') ? $request->session()->get('userprofile') : "");
        $viewData['userrights'] = ($request->session()->has('userrights') ? (json_decode($request->session()->get('userrights'), true)) : []);
        $viewData['userfirstname'] = ($request->session()->has('userfirstname') ? $request->session()->get('userfirstname') : "");
        $viewData['userlastname'] = ($request->session()->has('userlastname') ? $request->session()->get('userlastname') : "");
    }
}