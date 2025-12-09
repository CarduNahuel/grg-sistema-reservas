<?php

namespace App\Models;

class Reservation extends Model
{
    protected $table = 'reservations';

    /**
     * Get active reservation for user and restaurant
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
