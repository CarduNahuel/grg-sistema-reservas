<?php

namespace App\Models;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $fillable = [
        'restaurant_id', 'table_id', 'preferred_table_id', 'preferred_zone', 'user_id', 'reservation_date',
        'start_time', 'end_time', 'guest_count', 'status',
        'special_requests', 'confirmed_by', 'confirmed_at', 'check_in_time', 'notes'
    ];

    public function getByUser($userId)
    {
        $sql = "SELECT r.*, 
                       res.name as restaurant_name, res.address,
                       t.table_number, t.area
                FROM {$this->table} r
                INNER JOIN restaurants res ON r.restaurant_id = res.id
                LEFT JOIN tables t ON r.table_id = t.id
                WHERE r.user_id = ?
                ORDER BY r.start_time DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function getByRestaurant($restaurantId, $filters = [])
    {
        $sql = "SELECT r.*, 
                       t.table_number, t.area,
                       u.first_name, u.last_name, u.email, u.phone
                FROM {$this->table} r
                LEFT JOIN tables t ON r.table_id = t.id
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.restaurant_id = ?";
        
        $params = [$restaurantId];
        
        if (isset($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date'])) {
            $sql .= " AND r.reservation_date = ?";
            $params[] = $filters['date'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND r.reservation_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND r.reservation_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY r.start_time DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getUpcoming($userId, $limit = 5)
    {
        $sql = "SELECT r.*, 
                       res.name as restaurant_name, res.address,
                       t.table_number, t.area
                FROM {$this->table} r
                INNER JOIN restaurants res ON r.restaurant_id = res.id
                LEFT JOIN tables t ON r.table_id = t.id
                WHERE r.user_id = ?
                AND r.start_time >= NOW()
                AND r.status IN ('confirmed', 'pending')
                ORDER BY r.start_time ASC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    public function getPast($userId, $limit = 10)
    {
        $sql = "SELECT r.*, 
                       res.name as restaurant_name, res.address,
                       t.table_number, t.area
                FROM {$this->table} r
                INNER JOIN restaurants res ON r.restaurant_id = res.id
                LEFT JOIN tables t ON r.table_id = t.id
                WHERE r.user_id = ?
                AND r.start_time < NOW()
                ORDER BY r.start_time DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    public function confirm($reservationId, $confirmedBy)
    {
        return $this->update($reservationId, [
            'status' => 'confirmed',
            'confirmed_by' => $confirmedBy,
            'confirmed_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function reject($reservationId, $confirmedBy, $reason = null)
    {
        $data = [
            'status' => 'rejected',
            'confirmed_by' => $confirmedBy,
            'confirmed_at' => date('Y-m-d H:i:s')
        ];
        
        if ($reason) {
            $data['notes'] = $reason;
        }
        
        return $this->update($reservationId, $data);
    }

    public function cancel($reservationId)
    {
        return $this->update($reservationId, ['status' => 'cancelled']);
    }

    public function checkIn($reservationId)
    {
        return $this->update($reservationId, [
            'check_in_time' => date('Y-m-d H:i:s')
        ]);
    }

    public function complete($reservationId)
    {
        return $this->update($reservationId, ['status' => 'completed']);
    }

    public function markNoShow($reservationId)
    {
        return $this->update($reservationId, ['status' => 'no_show']);
    }

    public function reassign($reservationId, $newTableId)
    {
        // Check if new table is available
        $reservation = $this->find($reservationId);
        
        $tableModel = new Table();
        $isAvailable = $tableModel->isAvailable(
            $newTableId,
            $reservation['reservation_date'],
            $reservation['start_time'],
            $reservation['end_time']
        );
        
        if (!$isAvailable) {
            throw new \Exception("La mesa seleccionada no está disponible en ese horario.");
        }
        
        return $this->update($reservationId, ['table_id' => $newTableId]);
    }

    public function getNoShows($restaurantId, $dateFrom = null, $dateTo = null)
    {
        $sql = "SELECT r.*, 
                       u.first_name, u.last_name, u.email
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.restaurant_id = ?
                AND r.status = 'no_show'";
        
        $params = [$restaurantId];
        
        if ($dateFrom) {
            $sql .= " AND r.reservation_date >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $sql .= " AND r.reservation_date <= ?";
            $params[] = $dateTo;
        }
        
        $sql .= " ORDER BY r.start_time DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function checkForNoShows()
    {
        // Find reservations that should have started but client didn't check in
        $tolerance = 15; // minutes
        
        $sql = "SELECT * FROM {$this->table}
                WHERE status = 'confirmed'
                AND check_in_time IS NULL
                AND start_time < DATE_SUB(NOW(), INTERVAL ? MINUTE)
                AND DATE(start_time) = CURDATE()";
        
        $noShows = $this->db->fetchAll($sql, [$tolerance]);
        
        foreach ($noShows as $reservation) {
            $this->markNoShow($reservation['id']);
        }
        
        return count($noShows);
    }

    public function getReminders($minutesBefore = 60)
    {
        // Get reservations that need reminders
        $sql = "SELECT r.*, 
                       u.email, u.first_name, u.last_name,
                       res.name as restaurant_name
                FROM {$this->table} r
                INNER JOIN users u ON r.user_id = u.id
                INNER JOIN restaurants res ON r.restaurant_id = res.id
                WHERE r.status = 'confirmed'
                AND r.start_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? MINUTE)
                AND r.id NOT IN (
                    SELECT reservation_id FROM notifications
                    WHERE type = 'reservation_reminder'
                    AND reservation_id IS NOT NULL
                )";
        
        return $this->db->fetchAll($sql, [$minutesBefore]);
    }

    /**
     * Get active reservation for user and restaurant (for cart linking)
     */
    public function getUserActiveReservation($userId, $restaurantId)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = ? AND restaurant_id = ?
                AND status IN ('pending', 'confirmed')
                AND start_time >= NOW()
                ORDER BY start_time ASC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$userId, $restaurantId]);
    }
}
