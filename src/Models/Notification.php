<?php

namespace App\Models;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $fillable = [
        'user_id', 'reservation_id', 'type', 'title', 'message',
        'is_read', 'email_sent', 'email_sent_at'
    ];

    public function getByUser($userId, $unreadOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ?";
        $params = [$userId];
        
        if ($unreadOnly) {
            $sql .= " AND is_read = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, ['is_read' => true]);
    }

    public function markAllAsRead($userId)
    {
        $sql = "UPDATE {$this->table} SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE";
        $this->db->query($sql, [$userId]);
    }

    public function getUnreadCount($userId)
    {
        return $this->count('user_id = ? AND is_read = FALSE', [$userId]);
    }

    public function createReservationNotification($type, $userId, $reservationId, $title, $message)
    {
        return $this->create([
            'user_id' => $userId,
            'reservation_id' => $reservationId,
            'type' => $type,
            'title' => $title,
            'message' => $message
        ]);
    }

    public function markEmailSent($notificationId)
    {
        return $this->update($notificationId, [
            'email_sent' => true,
            'email_sent_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getPendingEmails()
    {
        $sql = "SELECT n.*, u.email, u.first_name, u.last_name
                FROM {$this->table} n
                INNER JOIN users u ON n.user_id = u.id
                WHERE n.email_sent = FALSE
                ORDER BY n.created_at ASC
                LIMIT 50";
        
        return $this->db->fetchAll($sql);
    }
}
