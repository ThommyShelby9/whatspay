<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Traits\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceAdminController extends Controller
{
    use Utils;
    
    public function index(Request $request)
    {
        $viewData = []; 
        $alert = []; 
        $this->setAlert($request, $alert);
        
        if (!$this->isConnected()) {
            return redirect(config('app.url').'/admin/login')->with($alert);
        }
        
        // Statistiques financières globales
        $viewData['totalRevenue'] = DB::selectOne("
            SELECT coalesce(SUM(gain), 0) as total FROM assignments WHERE status = 'COMPLETED'
        ")->total;
        
        $viewData['platformCommission'] = $viewData['totalRevenue'] * 0.1; // Commission de 10%
        
        $viewData['pendingPayments'] = DB::selectOne("
            SELECT COUNT(*) as count, coalesce(SUM(gain), 0) as total 
            FROM assignments 
            WHERE status = 'COMPLETED' AND payment_status IS NULL
        ");
        
        // Revenus mensuels pour le graphique
        $viewData['monthlyRevenue'] = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = date('m', strtotime("-$i months"));
            $year = date('Y', strtotime("-$i months"));
            $monthName = date('M', strtotime("-$i months"));
            
            $revenue = DB::selectOne("
                SELECT coalesce(SUM(gain), 0) as total
                FROM assignments
                WHERE MONTH(submission_date) = ? AND YEAR(submission_date) = ? AND status = 'COMPLETED'
            ", [$month, $year]);
            
            $viewData['monthlyRevenue'][] = [
                'month' => $monthName,
                'revenue' => $revenue->total
            ];
        }
        
        // Transactions récentes
        $viewData['recentTransactions'] = DB::select("
            SELECT 
                a.id, 
                a.submission_date, 
                a.gain, 
                a.status,
                a.payment_date,
                t.name as task_name,
                u.firstname as agent_firstname,
                u.lastname as agent_lastname,
                c.firstname as client_firstname,
                c.lastname as client_lastname
            FROM assignments a
            JOIN tasks t ON a.task_id = t.id
            JOIN users u ON a.agent_id = u.id
            JOIN users c ON t.client_id = c.id
            WHERE a.status = 'COMPLETED'
            ORDER BY a.submission_date DESC
            LIMIT 10
        ");
        
        $this->setViewData($request, $viewData);
        
        return view('admin.finance', [
            'alert' => $alert, 
            'viewData' => $viewData, 
            'version' => gmdate("YmdHis"),
            'title' => 'WhatsPAY | Admin', 
            'pagetilte' => 'Finance et paiements', 
            'pagecardtilte' => 'Vue d\'ensemble',
        ]);
    }
    
    // Les autres méthodes (transactions, validatePayment, etc.)
    // ...
    
    private function isConnected()
    {
        return (\Auth::viaRemember() || \Auth::check());
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
