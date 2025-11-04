<?php

namespace App\Services;

use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Traits\Utils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SupportService
{
    use Utils;
    
    /**
     * Récupère les tickets avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Liste des tickets
     */
    public function getTickets($filters = [])
    {
        $query = DB::table('tickets')
            ->leftJoin('users', 'tickets.user_id', '=', 'users.id')
            ->leftJoin('users as assigned', 'tickets.assigned_to', '=', 'assigned.id')
            ->select(
                'tickets.*',
                'users.firstname',
                'users.lastname',
                'users.email',
                'assigned.firstname as assigned_firstname',
                'assigned.lastname as assigned_lastname'
            );
            
        // Application des filtres
        if (!empty($filters['status'])) {
            $query->where('tickets.status', $filters['status']);
        }
        
        if (!empty($filters['priority'])) {
            $query->where('tickets.priority', $filters['priority']);
        }
        
        if (!empty($filters['category'])) {
            $query->where('tickets.category', $filters['category']);
        }
        
        // Tri par date de mise à jour décroissante
        $query->orderBy('tickets.updated_at', 'desc');
        
        return $query->get();
    }
    
    /**
     * Récupère un ticket par son ID
     * 
     * @param string $id ID du ticket
     * @return object|null Ticket ou null si non trouvé
     */
    public function getTicketById($id)
    {
        $tickets = DB::select("
            SELECT
                t.*,
                u.firstname,
                u.lastname,
                u.email,
                a.firstname as assigned_firstname,
                a.lastname as assigned_lastname
            FROM
                tickets t
            LEFT JOIN
                users u ON t.user_id = u.id
            LEFT JOIN
                users a ON t.assigned_to = a.id
            WHERE
                t.id = ?
        ", [$id]);
        
        return count($tickets) > 0 ? $tickets[0] : null;
    }
    
    /**
     * Récupère les messages d'un ticket
     * 
     * @param string $ticketId ID du ticket
     * @return array Liste des messages
     */
    public function getTicketMessages($ticketId)
    {
        return DB::table('ticket_messages')
            ->leftJoin('users', 'ticket_messages.user_id', '=', 'users.id')
            ->select(
                'ticket_messages.*',
                'users.firstname',
                'users.lastname',
                'users.email'
            )
            ->where('ticket_messages.ticket_id', $ticketId)
            ->orderBy('ticket_messages.created_at', 'asc')
            ->get();
    }
    
    /**
     * Récupère les tickets précédents d'un utilisateur
     * 
     * @param string $userId ID de l'utilisateur
     * @param string $excludeTicketId ID du ticket à exclure
     * @return array Liste des tickets
     */
    public function getUserPreviousTickets($userId, $excludeTicketId = null)
    {
        $query = DB::table('tickets')
            ->where('user_id', $userId);
            
        if ($excludeTicketId) {
            $query->where('id', '!=', $excludeTicketId);
        }
        
        return $query->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
    
    /**
     * Ajoute une réponse à un ticket
     * 
     * @param string $ticketId ID du ticket
     * @param array $data Données de la réponse
     * @return array Résultat de l'opération
     */
    public function addTicketReply($ticketId, $data)
    {
        try {
            $ticket = $this->getTicketById($ticketId);
            
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket non trouvé'
                ];
            }
            
            DB::beginTransaction();
            
            // Créer le message
            $messageId = $this->getId();
            
            $ticketMessage = [
                'id' => $messageId,
                'ticket_id' => $ticketId,
                'user_id' => $data['user_id'],
                'message' => $data['message'],
                'is_admin' => $data['is_admin'] ?? false,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            DB::table('ticket_messages')->insert($ticketMessage);
            
            // Gérer les pièces jointes si présentes
            if (!empty($data['attachments'])) {
                $attachments = [];
                
                foreach ($data['attachments'] as $attachment) {
                    $filename = $messageId . '_' . $attachment->getClientOriginalName();
                    $path = 'tickets/' . $ticketId . '/attachments';
                    
                    $attachment->storeAs($path, $filename, 'public');
                    
                    $attachments[] = [
                        'path' => $path . '/' . $filename,
                        'name' => $attachment->getClientOriginalName(),
                        'type' => $attachment->getClientMimeType(),
                        'size' => $attachment->getSize()
                    ];
                }
                
                // Enregistrer les informations des pièces jointes
                DB::table('ticket_messages')
                    ->where('id', $messageId)
                    ->update([
                        'attachments' => json_encode($attachments)
                    ]);
            }
            
            // Mettre à jour le statut du ticket si c'est une réponse admin
            if ($data['is_admin']) {
                DB::table('tickets')
                    ->where('id', $ticketId)
                    ->update([
                        'status' => 'answered',
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('tickets')
                    ->where('id', $ticketId)
                    ->update([
                        'status' => 'open',
                        'updated_at' => now()
                    ]);
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Réponse ajoutée avec succès',
                'message_id' => $messageId
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'ajout de la réponse: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Met à jour le statut d'un ticket
     * 
     * @param string $id ID du ticket
     * @param string $status Nouveau statut
     * @param string $adminId ID de l'administrateur
     * @return array Résultat de l'opération
     */
    public function updateTicketStatus($id, $status, $adminId)
    {
        try {
            $ticket = $this->getTicketById($id);
            
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket non trouvé'
                ];
            }
            
            DB::table('tickets')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'updated_at' => now(),
                    'updated_by' => $adminId
                ]);
                
            // Si le ticket est fermé, enregistrer la date de fermeture
            if ($status === 'closed') {
                DB::table('tickets')
                    ->where('id', $id)
                    ->update([
                        'closed_at' => now(),
                        'closed_by' => $adminId
                    ]);
            }
            
            // Ajouter un message système dans le ticket
            $statusLabels = [
                'open' => 'Ouvert',
                'in_progress' => 'En cours de traitement',
                'answered' => 'Répondu',
                'pending' => 'En attente',
                'closed' => 'Fermé',
                'reopened' => 'Réouvert'
            ];
            
            $statusLabel = $statusLabels[$status] ?? $status;
            
            $this->addTicketReply($id, [
                'user_id' => $adminId,
                'message' => "Statut mis à jour: $statusLabel",
                'is_admin' => true
            ]);
            
            return [
                'success' => true,
                'message' => 'Statut du ticket mis à jour avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Met à jour la priorité d'un ticket
     * 
     * @param string $id ID du ticket
     * @param string $priority Nouvelle priorité
     * @param string $adminId ID de l'administrateur
     * @return array Résultat de l'opération
     */
    public function updateTicketPriority($id, $priority, $adminId)
    {
        try {
            $ticket = $this->getTicketById($id);
            
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket non trouvé'
                ];
            }
            
            DB::table('tickets')
                ->where('id', $id)
                ->update([
                    'priority' => $priority,
                    'updated_at' => now(),
                    'updated_by' => $adminId
                ]);
                
            // Ajouter un message système dans le ticket
            $priorityLabels = [
                'low' => 'Basse',
                'medium' => 'Moyenne',
                'high' => 'Haute',
                'urgent' => 'Urgente'
            ];
            
            $priorityLabel = $priorityLabels[$priority] ?? $priority;
            
            $this->addTicketReply($id, [
                'user_id' => $adminId,
                'message' => "Priorité mise à jour: $priorityLabel",
                'is_admin' => true
            ]);
            
            return [
                'success' => true,
                'message' => 'Priorité du ticket mise à jour avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la priorité: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Assigne un ticket à un administrateur
     * 
     * @param string $id ID du ticket
     * @param string $adminId ID de l'administrateur assigné
     * @param string $updatedBy ID de l'administrateur effectuant l'assignation
     * @return array Résultat de l'opération
     */
    public function assignTicket($id, $adminId, $updatedBy)
    {
        try {
            $ticket = $this->getTicketById($id);
            
            if (!$ticket) {
                return [
                    'success' => false,
                    'message' => 'Ticket non trouvé'
                ];
            }
            
            DB::table('tickets')
                ->where('id', $id)
                ->update([
                    'assigned_to' => $adminId,
                    'updated_at' => now(),
                    'updated_by' => $updatedBy
                ]);
                
            // Obtenir les informations de l'administrateur assigné
            $admin = DB::table('users')
                ->where('id', $adminId)
                ->first();
                
            // Ajouter un message système dans le ticket
            $this->addTicketReply($id, [
                'user_id' => $updatedBy,
                'message' => "Ticket assigné à {$admin->firstname} {$admin->lastname}",
                'is_admin' => true
            ]);
            
            return [
                'success' => true,
                'message' => 'Ticket assigné avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de l\'assignation du ticket: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Récupère les statistiques des tickets
     * 
     * @return array Statistiques des tickets
     */
    public function getTicketStats()
    {
        $stats = [
            'total' => DB::table('tickets')->count(),
            'open' => DB::table('tickets')->where('status', 'open')->count(),
            'in_progress' => DB::table('tickets')->where('status', 'in_progress')->count(),
            'answered' => DB::table('tickets')->where('status', 'answered')->count(),
            'pending' => DB::table('tickets')->where('status', 'pending')->count(),
            'closed' => DB::table('tickets')->where('status', 'closed')->count(),
            'high_priority' => DB::table('tickets')->whereIn('priority', ['high', 'urgent'])->count(),
            'average_resolution_time' => null
        ];
        
        // Calculer le temps moyen de résolution
        $resolvedTickets = DB::select("
            SELECT
                AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_resolution_time
            FROM
                tickets
            WHERE
                status = 'closed'
                AND closed_at IS NOT NULL
        ");
        
        if (count($resolvedTickets) > 0 && $resolvedTickets[0]->avg_resolution_time) {
            $stats['average_resolution_time'] = round($resolvedTickets[0]->avg_resolution_time, 2);
        }
        
        return $stats;
    }
    
    /**
     * Récupère les statuts des tickets
     * 
     * @return array Statuts des tickets
     */
    public function getTicketStatuses()
    {
        return [
            'open' => 'Ouvert',
            'in_progress' => 'En cours de traitement',
            'answered' => 'Répondu',
            'pending' => 'En attente',
            'closed' => 'Fermé',
            'reopened' => 'Réouvert'
        ];
    }
    
    /**
     * Récupère les priorités des tickets
     * 
     * @return array Priorités des tickets
     */
    public function getTicketPriorities()
    {
        return [
            'low' => 'Basse',
            'medium' => 'Moyenne',
            'high' => 'Haute',
            'urgent' => 'Urgente'
        ];
    }
    
    /**
     * Récupère les catégories des tickets
     * 
     * @return array Catégories des tickets
     */
    public function getTicketCategories()
    {
        return [
            'general' => 'Général',
            'technical' => 'Technique',
            'billing' => 'Facturation',
            'account' => 'Compte',
            'campaign' => 'Campagne',
            'payment' => 'Paiement',
            'whatsapp' => 'WhatsApp',
            'other' => 'Autre'
        ];
    }
    
    /**
     * Récupère les catégories de FAQ
     * 
     * @return array Catégories de FAQ
     */
    public function getFaqCategories()
    {
        return FaqCategory::orderBy('order', 'asc')->get();
    }
    
    /**
     * Récupère les éléments de FAQ avec filtres
     * 
     * @param array $filters Filtres à appliquer
     * @return array Éléments de FAQ
     */
    public function getFaqItems($filters = [])
    {
        $query = DB::table('faqs')
            ->leftJoin('faq_categories', 'faqs.category_id', '=', 'faq_categories.id')
            ->select(
                'faqs.*',
                'faq_categories.name as category_name'
            );
            
        // Application des filtres
        if (!empty($filters['category_id'])) {
            $query->where('faqs.category_id', $filters['category_id']);
        }
        
        if (isset($filters['is_active'])) {
            $query->where('faqs.is_active', $filters['is_active']);
        }
        
        // Tri par ordre croissant
        $query->orderBy('faq_categories.order', 'asc')
            ->orderBy('faqs.order', 'asc');
            
        return $query->get();
    }
    
    /**
     * Récupère un élément de FAQ par son ID
     * 
     * @param string $id ID de l'élément de FAQ
     * @return object|null Élément de FAQ ou null si non trouvé
     */
    public function getFaqItemById($id)
    {
        $faqs = DB::select("
            SELECT
                f.*,
                fc.name as category_name
            FROM
                faqs f
            LEFT JOIN
                faq_categories fc ON f.category_id = fc.id
            WHERE
                f.id = ?
        ", [$id]);
        
        return count($faqs) > 0 ? $faqs[0] : null;
    }
    
    /**
     * Crée un élément de FAQ
     * 
     * @param array $data Données de l'élément de FAQ
     * @return array Résultat de l'opération
     */
    public function createFaqItem($data)
    {
        try {
            $faqId = $this->getId();
            
            Faq::create([
                'id' => $faqId,
                'question' => $data['question'],
                'answer' => $data['answer'],
                'category_id' => $data['category_id'],
                'order' => $data['order'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $data['created_by'] ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => 'Élément de FAQ créé avec succès',
                'faq_id' => $faqId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de l\'élément de FAQ: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Met à jour un élément de FAQ
     * 
     * @param string $id ID de l'élément de FAQ
     * @param array $data Données de l'élément de FAQ
     * @return array Résultat de l'opération
     */
    public function updateFaqItem($id, $data)
    {
        try {
            $faq = Faq::find($id);
            
            if (!$faq) {
                return [
                    'success' => false,
                    'message' => 'Élément de FAQ non trouvé'
                ];
            }
            
            $faq->update([
                'question' => $data['question'],
                'answer' => $data['answer'],
                'category_id' => $data['category_id'],
                'order' => $data['order'] ?? $faq->order,
                'is_active' => $data['is_active'] ?? $faq->is_active,
                'updated_by' => $data['updated_by'] ?? null,
                'updated_at' => now()
            ]);
            
            return [
                'success' => true,
                'message' => 'Élément de FAQ mis à jour avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'élément de FAQ: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Supprime un élément de FAQ
     * 
     * @param string $id ID de l'élément de FAQ
     * @return array Résultat de l'opération
     */
    public function deleteFaqItem($id)
    {
        try {
            $faq = Faq::find($id);
            
            if (!$faq) {
                return [
                    'success' => false,
                    'message' => 'Élément de FAQ non trouvé'
                ];
            }
            
            $faq->delete();
            
            return [
                'success' => true,
                'message' => 'Élément de FAQ supprimé avec succès'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'élément de FAQ: ' . $e->getMessage()
            ];
        }
    }
}