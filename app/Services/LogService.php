<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\SecurityLog;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogService
{
    use Utils;
    
    /**
     * Récupère les logs d'activité avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Logs d'activité
     */
    public function getActivityLogs($filters = [])
    {
        $query = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(
                'activity_logs.*',
                'users.firstname',
                'users.lastname',
                'users.email'
            );
            
        // Application des filtres
        if (!empty($filters['type'])) {
            $query->where('activity_logs.type', $filters['type']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('activity_logs.user_id', $filters['user_id']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('activity_logs.created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('activity_logs.created_at', '<=', $filters['end_date']);
        }
        
        // Tri par date décroissante
        $query->orderBy('activity_logs.created_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Récupère les logs système avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Logs système
     */
    public function getSystemLogs($filters = [])
    {
        $query = DB::table('system_logs');
            
        // Application des filtres
        if (!empty($filters['level'])) {
            $query->where('system_logs.level', $filters['level']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('system_logs.created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('system_logs.created_at', '<=', $filters['end_date']);
        }
        
        // Tri par date décroissante
        $query->orderBy('system_logs.created_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Récupère les logs de sécurité avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Logs de sécurité
     */
    public function getSecurityLogs($filters = [])
    {
        $query = DB::table('security_logs')
            ->leftJoin('users', 'security_logs.user_id', '=', 'users.id')
            ->select(
                'security_logs.*',
                'users.firstname',
                'users.lastname',
                'users.email'
            );
            
        // Application des filtres
        if (!empty($filters['type'])) {
            $query->where('security_logs.type', $filters['type']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('security_logs.user_id', $filters['user_id']);
        }
        
        if (!empty($filters['ip'])) {
            $query->where('security_logs.ip_address', 'like', "%{$filters['ip']}%");
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('security_logs.created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('security_logs.created_at', '<=', $filters['end_date']);
        }
        
        // Tri par date décroissante
        $query->orderBy('security_logs.created_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Récupère les logs d'audit avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Logs d'audit
     */
    public function getAuditLogs($filters = [])
    {
        $query = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select(
                'audit_logs.*',
                'users.firstname',
                'users.lastname',
                'users.email'
            );
            
        // Application des filtres
        if (!empty($filters['entity'])) {
            $query->where('audit_logs.entity_type', $filters['entity']);
        }
        
        if (!empty($filters['action'])) {
            $query->where('audit_logs.action', $filters['action']);
        }
        
        if (!empty($filters['user_id'])) {
            $query->where('audit_logs.user_id', $filters['user_id']);
        }
        
        if (!empty($filters['start_date'])) {
            $query->where('audit_logs.created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('audit_logs.created_at', '<=', $filters['end_date']);
        }
        
        // Tri par date décroissante
        $query->orderBy('audit_logs.created_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Récupère les logs d'erreurs avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Logs d'erreurs
     */
    public function getErrorLogs($filters = [])
    {
        $level = $filters['level'] ?? 'error';
        
        $query = DB::table('system_logs')
            ->where('level', $level);
            
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }
        
        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }
        
        // Tri par date décroissante
        $query->orderBy('created_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Enregistre un log d'activité
     * 
     * @param array $data Données du log
     * @return array Résultat de l'opération
     */
    public function logActivity($data)
    {
        try {
            $logId = $this->getId();
            
            ActivityLog::create([
                'id' => $logId,
                'user_id' => $data['user_id'] ?? null,
                'type' => $data['type'],
                'description' => $data['description'],
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'details' => json_encode($data['details'] ?? [])
            ]);
            
            return [
                'success' => true,
                'message' => 'Log d\'activité enregistré avec succès',
                'log_id' => $logId
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du log d\'activité: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du log d\'activité'
            ];
        }
    }
    
    /**
     * Enregistre un log de sécurité
     * 
     * @param array $data Données du log
     * @return array Résultat de l'opération
     */
    public function logSecurity($data)
    {
        try {
            $logId = $this->getId();
            
            SecurityLog::create([
                'id' => $logId,
                'user_id' => $data['user_id'] ?? null,
                'type' => $data['type'],
                'description' => $data['description'],
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'severity' => $data['severity'] ?? 'info',
                'details' => json_encode($data['details'] ?? [])
            ]);
            
            return [
                'success' => true,
                'message' => 'Log de sécurité enregistré avec succès',
                'log_id' => $logId
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du log de sécurité: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du log de sécurité'
            ];
        }
    }
    
    /**
     * Enregistre un log d'audit
     * 
     * @param array $data Données du log
     * @return array Résultat de l'opération
     */
    public function logAudit($data)
    {
        try {
            $logId = $this->getId();
            
            AuditLog::create([
                'id' => $logId,
                'user_id' => $data['user_id'] ?? null,
                'entity_type' => $data['entity_type'],
                'entity_id' => $data['entity_id'],
                'action' => $data['action'],
                'old_values' => json_encode($data['old_values'] ?? []),
                'new_values' => json_encode($data['new_values'] ?? []),
                'ip_address' => $data['ip_address'] ?? null
            ]);
            
            return [
                'success' => true,
                'message' => 'Log d\'audit enregistré avec succès',
                'log_id' => $logId
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du log d\'audit: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du log d\'audit'
            ];
        }
    }
    
    /**
     * Efface les logs
     * 
     * @param string $type Type de logs (activity, system, security, audit, error)
     * @param array $params Paramètres supplémentaires
     * @return array Résultat de l'opération
     */
    public function clearLogs($type, $params = [])
    {
        try {
            $beforeDate = $params['before_date'] ?? null;
            $adminId = $params['admin_id'] ?? null;
            
            $query = null;
            
            switch ($type) {
                case 'activity':
                    $query = DB::table('activity_logs');
                    break;
                case 'system':
                    $query = DB::table('system_logs');
                    break;
                case 'security':
                    $query = DB::table('security_logs');
                    break;
                case 'audit':
                    $query = DB::table('audit_logs');
                    break;
                case 'error':
                    $query = DB::table('system_logs')->where('level', 'error');
                    break;
                default:
                    return [
                        'success' => false,
                        'message' => 'Type de logs non reconnu'
                    ];
            }
            
            if ($beforeDate) {
                $query->where('created_at', '<', $beforeDate);
            }
            
            $count = $query->count();
            
            if ($count === 0) {
                return [
                    'success' => false,
                    'message' => 'Aucun log à effacer'
                ];
            }
            
            $query->delete();
            
            // Journaliser l'action
            if ($adminId) {
                $this->logActivity([
                    'user_id' => $adminId,
                    'type' => 'log_clear',
                    'description' => "Effacement de $count logs de type $type" . ($beforeDate ? " antérieurs à $beforeDate" : ""),
                    'details' => [
                        'log_type' => $type,
                        'count' => $count,
                        'before_date' => $beforeDate
                    ]
                ]);
            }
            
            return [
                'success' => true,
                'message' => "$count logs ont été effacés",
                'count' => $count
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'effacement des logs: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'effacement des logs: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Exporte les logs
     * 
     * @param string $type Type de logs (activity, system, security, audit, error)
     * @param array $filters Filtres à appliquer
     * @param string $format Format d'export (csv, excel, pdf)
     * @return mixed Fichier d'export
     */
    public function exportLogs($type, $filters = [], $format = 'csv')
    {
        // Récupérer les logs selon le type
        $logs = [];
        
        switch ($type) {
            case 'activity':
                $logs = $this->getActivityLogs($filters);
                break;
            case 'system':
                $logs = $this->getSystemLogs($filters);
                break;
            case 'security':
                $logs = $this->getSecurityLogs($filters);
                break;
            case 'audit':
                $logs = $this->getAuditLogs($filters);
                break;
            case 'error':
                $logs = $this->getErrorLogs($filters);
                break;
            default:
                return [
                    'success' => false,
                    'message' => 'Type de logs non reconnu'
                ];
        }
        
        // Implémenter l'export selon le format demandé
        // Cela nécessiterait l'utilisation de bibliothèques comme Laravel Excel
        
        // Pour l'instant, retournons un tableau de données
        return [
            'logs' => $logs,
            'filters' => $filters,
            'format' => $format
        ];
    }
    
    /**
     * Récupère les types de logs
     * 
     * @return array Types de logs
     */
    public function getLogTypes()
    {
        return [
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'create' => 'Création',
            'update' => 'Mise à jour',
            'delete' => 'Suppression',
            'view' => 'Consultation',
            'export' => 'Export',
            'import' => 'Import',
            'payment' => 'Paiement',
            'email' => 'Email',
            'admin' => 'Action admin',
            'other' => 'Autre'
        ];
    }
    
    /**
     * Récupère les niveaux de logs
     * 
     * @return array Niveaux de logs
     */
    public function getLogLevels()
    {
        return [
            'debug' => 'Debug',
            'info' => 'Information',
            'notice' => 'Notification',
            'warning' => 'Avertissement',
            'error' => 'Erreur',
            'critical' => 'Critique',
            'alert' => 'Alerte',
            'emergency' => 'Urgence'
        ];
    }
    
    /**
     * Récupère les types de logs de sécurité
     * 
     * @return array Types de logs de sécurité
     */
    public function getSecurityLogTypes()
    {
        return [
            'login_success' => 'Connexion réussie',
            'login_failed' => 'Échec de connexion',
            'password_reset' => 'Réinitialisation de mot de passe',
            'account_locked' => 'Compte verrouillé',
            'permission_change' => 'Changement de permissions',
            'suspicious_activity' => 'Activité suspecte',
            'brute_force' => 'Tentative de force brute',
            'api_key_change' => 'Changement de clé API',
            '2fa_change' => 'Changement de 2FA',
            'ip_blocked' => 'IP bloquée',
            'admin_action' => 'Action administrative'
        ];
    }
    
    /**
     * Récupère les types d'entités pour les logs d'audit
     * 
     * @return array Types d'entités
     */
    public function getEntityTypes()
    {
        return [
            'user' => 'Utilisateur',
            'task' => 'Tâche/Campagne',
            'category' => 'Catégorie',
            'payment' => 'Paiement',
            'transaction' => 'Transaction',
            'assignment' => 'Affectation',
            'setting' => 'Paramètre',
            'content' => 'Contenu',
            'role' => 'Rôle',
            'permission' => 'Permission',
            'phone' => 'Téléphone'
        ];
    }
    
    /**
     * Récupère les types d'actions pour les logs d'audit
     * 
     * @return array Types d'actions
     */
    public function getActionTypes()
    {
        return [
            'create' => 'Création',
            'update' => 'Mise à jour',
            'delete' => 'Suppression',
            'restore' => 'Restauration',
            'approve' => 'Approbation',
            'reject' => 'Rejet',
            'complete' => 'Finalisation',
            'cancel' => 'Annulation',
            'assign' => 'Assignation',
            'unassign' => 'Désassignation'
        ];
    }
}