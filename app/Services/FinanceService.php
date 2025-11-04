<?php

namespace App\Services;

use App\Consts\Util;
use App\Models\Payment;
use App\Models\Transaction;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    use Utils;
    
    /**
     * Récupère un résumé financier global
     * 
     * @param string $startDate Date de début (format Y-m-d)
     * @param string $endDate Date de fin (format Y-m-d)
     * @return array Résumé financier
     */
    public function getFinancialSummary($startDate = null, $endDate = null)
    {
        // Si les dates ne sont pas fournies, on prend le mois en cours
        if (empty($startDate)) {
            $startDate = date('Y-m-01'); // Premier jour du mois
        }
        
        if (empty($endDate)) {
            $endDate = date('Y-m-t'); // Dernier jour du mois
        }
        
        // Statistiques globales
        $totalRevenue = DB::table('transactions')
            ->where('type', 'revenue')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
            
        $totalPayments = DB::table('transactions')
            ->where('type', 'payment')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
            
        $totalFees = DB::table('transactions')
            ->where('type', 'fee')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
            
        $totalRefunds = DB::table('transactions')
            ->where('type', 'refund')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
            
        // Paiements en attente
        $pendingPayments = DB::table('transactions')
            ->where('type', 'payment')
            ->where('status', 'pending')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
            
        // Net profit
        $netProfit = $totalRevenue - $totalPayments - $totalRefunds;
        
        // Revenus par méthode de paiement
        $revenueByMethod = DB::table('transactions')
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->where('type', 'revenue')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('payment_method')
            ->get();
            
        // Statistiques d'utilisation de la plateforme
        $activeCampaigns = DB::table('tasks')
            ->where('status', Util::TASKS_STATUSES['APPROVED'])
            ->count();
            
        $completedCampaigns = DB::table('tasks')
            ->where('status', Util::TASKS_STATUSES['COMPLETED'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        $newClients = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->join('users', 'role_user.user_id', '=', 'users.id')
            ->where('roles.typerole', Util::TYPES_ROLE['ANNONCEUR'])
            ->whereBetween('users.created_at', [$startDate, $endDate])
            ->count();
        
        return [
            'total_revenue' => $totalRevenue,
            'total_payments' => $totalPayments,
            'total_fees' => $totalFees,
            'total_refunds' => $totalRefunds,
            'pending_payments' => $pendingPayments,
            'net_profit' => $netProfit,
            'revenue_by_method' => $revenueByMethod,
            'active_campaigns' => $activeCampaigns,
            'completed_campaigns' => $completedCampaigns,
            'new_clients' => $newClients,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
    
    /**
     * Récupère les transactions avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Liste des transactions
     */
    public function getTransactions($filters = [])
    {
        $query = DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select(
                'transactions.*',
                'users.firstname',
                'users.lastname',
                'users.email'
            );
            
        // Application des filtres
        if (!empty($filters['type'])) {
            $query->where('transactions.type', $filters['type']);
        }
        
        if (!empty($filters['status'])) {
            $query->where('transactions.status', $filters['status']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('transactions.user_id', $filters['user_id']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('transactions.created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('transactions.created_at', '<=', $filters['end_date']);
        }
        
        // Tri par date décroissante
        $query->orderBy('transactions.created_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Récupère les transactions récentes
     * 
     * @param int $limit Nombre de transactions à récupérer
     * @return array Liste des transactions récentes
     */
    public function getRecentTransactions($limit = 10)
    {
        return DB::table('transactions')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->select(
                'transactions.*',
                'users.firstname',
                'users.lastname',
                'users.email'
            )
            ->orderBy('transactions.created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Récupère les paiements avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Liste des paiements
     */
    public function getPayments($filters = [])
    {
        $query = DB::table('payments')
            ->leftJoin('users', 'payments.user_id', '=', 'users.id')
            ->leftJoin('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->select(
                'payments.*',
                'users.firstname',
                'users.lastname',
                'users.email',
                'transactions.amount',
                'transactions.payment_method'
            );
            
        // Application des filtres
        if (!empty($filters['status'])) {
            $query->where('payments.status', $filters['status']);
        }
        
        if (!empty($filters['user_type'])) {
            $query->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
                ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                ->where('roles.typerole', $filters['user_type']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('payments.user_id', $filters['user_id']);
        }
        
        // Tri par date décroissante
        $query->orderBy('payments.created_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Récupère les paiements en attente
     * 
     * @param int $limit Nombre de paiements à récupérer
     * @return array Liste des paiements en attente
     */
    public function getPendingPayments($limit = 10)
    {
        return DB::table('payments')
            ->leftJoin('users', 'payments.user_id', '=', 'users.id')
            ->leftJoin('transactions', 'payments.transaction_id', '=', 'transactions.id')
            ->select(
                'payments.*',
                'users.firstname',
                'users.lastname',
                'users.email',
                'transactions.amount',
                'transactions.payment_method'
            )
            ->where('payments.status', 'pending')
            ->orderBy('payments.created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Approuve un paiement
     * 
     * @param string $id ID du paiement
     * @param string $adminId ID de l'administrateur
     * @return array Résultat de l'opération
     */
    public function approvePayment($id, $adminId)
    {
        try {
            $payment = DB::table('payments')->where('id', $id)->first();
            
            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Paiement non trouvé'
                ];
            }
            
            if ($payment->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Ce paiement a déjà été traité'
                ];
            }
            
            DB::beginTransaction();
            
            // Mettre à jour le statut du paiement
            DB::table('payments')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',
                    'processed_by' => $adminId,
                    'processed_at' => now()
                ]);
                
            // Mettre à jour le statut de la transaction associée
            DB::table('transactions')
                ->where('id', $payment->transaction_id)
                ->update([
                    'status' => 'completed',
                    'updated_at' => now()
                ]);
                
            // Si le paiement est lié à une tâche, mettre à jour le statut de la tâche
            if ($payment->task_id) {
                DB::table('tasks')
                    ->where('id', $payment->task_id)
                    ->update([
                        'status' => Util::TASKS_STATUSES['PAID'],
                        'updated_at' => now()
                    ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Paiement approuvé avec succès'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'approbation du paiement: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Rejette un paiement
     * 
     * @param string $id ID du paiement
     * @param string $adminId ID de l'administrateur
     * @param string $reason Raison du rejet
     * @return array Résultat de l'opération
     */
    public function rejectPayment($id, $adminId, $reason)
    {
        try {
            $payment = DB::table('payments')->where('id', $id)->first();
            
            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Paiement non trouvé'
                ];
            }
            
            if ($payment->status !== 'pending') {
                return [
                    'success' => false,
                    'message' => 'Ce paiement a déjà été traité'
                ];
            }
            
            DB::beginTransaction();
            
            // Mettre à jour le statut du paiement
            DB::table('payments')
                ->where('id', $id)
                ->update([
                    'status' => 'rejected',
                    'processed_by' => $adminId,
                    'processed_at' => now(),
                    'notes' => $reason
                ]);
                
            // Mettre à jour le statut de la transaction associée
            DB::table('transactions')
                ->where('id', $payment->transaction_id)
                ->update([
                    'status' => 'failed',
                    'updated_at' => now()
                ]);
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Paiement rejeté avec succès'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Erreur lors du rejet du paiement: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupère les données pour les rapports financiers
     * 
     * @param string $startDate Date de début (format Y-m-d)
     * @param string $endDate Date de fin (format Y-m-d)
     * @param string $reportType Type de rapport (daily, weekly, monthly, quarterly, yearly)
     * @return array Données financières
     */
    public function getFinancialReportData($startDate, $endDate, $reportType = 'monthly')
    {
        // Détermination du format de date en fonction du type de rapport
        $dateFormat = '%Y-%m-%d'; // Format par défaut (daily)
        $dateSelect = "DATE(created_at) as date";
        
        switch ($reportType) {
            case 'weekly':
                $dateFormat = '%Y-%u'; // Année-Semaine
                $dateSelect = "CONCAT(YEAR(created_at), '-', WEEK(created_at)) as date";
                break;
            case 'monthly':
                $dateFormat = '%Y-%m'; // Année-Mois
                $dateSelect = "DATE_FORMAT(created_at, '%Y-%m') as date";
                break;
            case 'quarterly':
                $dateFormat = '%Y-Q%q'; // Année-Trimestre
                $dateSelect = "CONCAT(YEAR(created_at), '-Q', QUARTER(created_at)) as date";
                break;
            case 'yearly':
                $dateFormat = '%Y'; // Année
                $dateSelect = "YEAR(created_at) as date";
                break;
        }
        
        // Revenus par période
        $revenue = DB::select("
            SELECT
                $dateSelect,
                SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN type = 'fee' THEN amount ELSE 0 END) as fees,
                SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as payments,
                SUM(CASE WHEN type = 'refund' THEN amount ELSE 0 END) as refunds
            FROM
                transactions
            WHERE
                status = 'completed'
                AND created_at BETWEEN ? AND ?
            GROUP BY
                date
            ORDER BY
                date ASC
        ", [$startDate, $endDate]);
        
        // Totaux
        $totals = [
            'revenue' => 0,
            'fees' => 0,
            'payments' => 0,
            'refunds' => 0,
            'net_profit' => 0
        ];
        
        foreach ($revenue as $entry) {
            $totals['revenue'] += $entry->revenue;
            $totals['fees'] += $entry->fees;
            $totals['payments'] += $entry->payments;
            $totals['refunds'] += $entry->refunds;
        }
        
        $totals['net_profit'] = $totals['revenue'] - $totals['payments'] - $totals['refunds'];
        
        // Répartition par méthode de paiement
        $paymentMethods = DB::select("
            SELECT
                payment_method,
                SUM(amount) as total,
                COUNT(*) as count
            FROM
                transactions
            WHERE
                type = 'revenue'
                AND status = 'completed'
                AND created_at BETWEEN ? AND ?
            GROUP BY
                payment_method
        ", [$startDate, $endDate]);
        
        // Répartition par type d'utilisateur
        $userRevenue = DB::select("
            SELECT
                r.typerole as user_type,
                SUM(t.amount) as total,
                COUNT(DISTINCT t.user_id) as user_count
            FROM
                transactions t
            JOIN
                users u ON t.user_id = u.id
            JOIN
                role_user ru ON u.id = ru.user_id
            JOIN
                roles r ON ru.role_id = r.id
            WHERE
                t.type = 'revenue'
                AND t.status = 'completed'
                AND t.created_at BETWEEN ? AND ?
            GROUP BY
                r.typerole
        ", [$startDate, $endDate]);
        
        return [
            'revenue_by_period' => $revenue,
            'totals' => $totals,
            'payment_methods' => $paymentMethods,
            'user_revenue' => $userRevenue,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'report_type' => $reportType
        ];
    }
    
    /**
     * Récupère les données pour les graphiques de revenus
     * 
     * @param string $startDate Date de début (format Y-m-d)
     * @param string $endDate Date de fin (format Y-m-d)
     * @return array Données pour les graphiques
     */
    public function getRevenueChartData($startDate, $endDate)
    {
        // Revenus journaliers sur la période
        $dailyRevenue = DB::select("
            SELECT
                DATE(created_at) as date,
                SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END) as revenue,
                SUM(CASE WHEN type = 'payment' THEN amount ELSE 0 END) as payments
            FROM
                transactions
            WHERE
                status = 'completed'
                AND created_at BETWEEN ? AND ?
            GROUP BY
                date
            ORDER BY
                date ASC
        ", [$startDate, $endDate]);
        
        return [
            'daily_revenue' => $dailyRevenue,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }
    
    /**
     * Récupère la répartition des paiements par méthode
     * 
     * @return array Répartition des paiements
     */
    public function getPaymentTypeDistribution()
    {
        return DB::select("
            SELECT
                payment_method,
                COUNT(*) as count,
                SUM(amount) as total
            FROM
                transactions
            WHERE
                type = 'revenue'
                AND status = 'completed'
            GROUP BY
                payment_method
        ");
    }
    
    /**
     * Exporte un rapport financier
     * 
     * @param string $startDate Date de début (format Y-m-d)
     * @param string $endDate Date de fin (format Y-m-d)
     * @param string $reportType Type de rapport (daily, weekly, monthly, quarterly, yearly)
     * @param string $format Format d'export (csv, excel, pdf)
     * @return mixed Fichier d'export
     */
    public function exportFinancialReport($startDate, $endDate, $reportType, $format)
    {
        // Récupérer les données
        $data = $this->getFinancialReportData($startDate, $endDate, $reportType);
        
        // Implémentation de l'export selon le format demandé
        // Cela nécessiterait l'utilisation de bibliothèques comme Laravel Excel
        
        // Pour l'instant, retournons un tableau de données
        return $data;
    }
    
    /**
     * Récupère les types de transactions
     * 
     * @return array Types de transactions
     */
    public function getTransactionTypes()
    {
        return [
            'revenue' => 'Revenu',
            'payment' => 'Paiement',
            'refund' => 'Remboursement',
            'fee' => 'Frais',
            'transfer' => 'Transfert'
        ];
    }
    
    /**
     * Récupère les statuts des transactions
     * 
     * @return array Statuts des transactions
     */
    public function getTransactionStatuses()
    {
        return [
            'pending' => 'En attente',
            'completed' => 'Complété',
            'failed' => 'Échoué',
            'canceled' => 'Annulé',
            'refunded' => 'Remboursé'
        ];
    }
    
    /**
     * Récupère les statuts des paiements
     * 
     * @return array Statuts des paiements
     */
    public function getPaymentStatuses()
    {
        return [
            'pending' => 'En attente',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'canceled' => 'Annulé'
        ];
    }
}