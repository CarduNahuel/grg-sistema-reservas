<?php

namespace App\Models;

class Restaurant extends Model
{
    protected $table = 'restaurants';
    protected $fillable = [
        'owner_id', 'name', 'description', 'address', 'city', 'state',
        'postal_code', 'phone', 'email', 'opening_time', 'closing_time',
        'image_url', 'is_active', 'requires_payment', 'payment_status'
    ];

    public function getActive()
    {
        return $this->where('is_active = TRUE');
    }

    public function getByOwner($ownerId)
    {
        $sql = "SELECT r.*, ru.role 
                FROM {$this->table} r
                INNER JOIN restaurant_users ru ON ru.restaurant_id = r.id
                WHERE ru.user_id = ?
                ORDER BY r.created_at DESC";
        return $this->db->fetchAll($sql, [$ownerId]);
    }

    public function getTables($restaurantId)
    {
        $sql = "SELECT * FROM tables 
                WHERE restaurant_id = ? 
                ORDER BY area, table_number";
        return $this->db->fetchAll($sql, [$restaurantId]);
    }

    public function getAvailableTables($restaurantId, $date, $startTime, $endTime)
    {
        $sql = "SELECT t.* FROM tables t
                WHERE t.restaurant_id = ? 
                AND t.is_available = TRUE
                AND t.id NOT IN (
                    SELECT table_id FROM reservations
                    WHERE restaurant_id = ?
                    AND reservation_date = ?
                    AND status IN ('confirmed', 'pending')
                    AND (
                        (start_time < ? AND end_time > ?)
                        OR (start_time >= ? AND start_time < ?)
                    )
                )
                ORDER BY t.area, t.capacity";
        
        return $this->db->fetchAll($sql, [
            $restaurantId, $restaurantId, $date,
            $endTime, $startTime,
            $startTime, $endTime
        ]);
    }

    public function getReservations($restaurantId, $status = null, $date = null)
    {
        $sql = "SELECT r.*, 
                       t.table_number, t.area,
                       u.first_name, u.last_name, u.email, u.phone
                FROM reservations r
                INNER JOIN tables t ON r.table_id = t.id
                INNER JOIN users u ON r.user_id = u.id
                WHERE r.restaurant_id = ?";
        
        $params = [$restaurantId];
        
        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }
        
        if ($date) {
            $sql .= " AND r.reservation_date = ?";
            $params[] = $date;
        }
        
        $sql .= " ORDER BY r.start_time DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getPendingReservations($restaurantId)
    {
        return $this->getReservations($restaurantId, 'pending');
    }

    public function getTodayReservations($restaurantId)
    {
        return $this->getReservations($restaurantId, null, date('Y-m-d'));
    }

    public function addUser($restaurantId, $userId, $role)
    {
        $sql = "INSERT INTO restaurant_users (restaurant_id, user_id, role) 
                VALUES (?, ?, ?)";
        return $this->db->insert($sql, [$restaurantId, $userId, $role]);
    }

    public function removeUser($restaurantId, $userId)
    {
        $sql = "DELETE FROM restaurant_users 
                WHERE restaurant_id = ? AND user_id = ?";
        $this->db->query($sql, [$restaurantId, $userId]);
    }

    public function getUsers($restaurantId)
    {
        $sql = "SELECT u.*, ru.role as restaurant_role, r.name as role_name
                FROM users u
                INNER JOIN restaurant_users ru ON ru.user_id = u.id
                INNER JOIN roles r ON r.id = u.role_id
                WHERE ru.restaurant_id = ?";
        return $this->db->fetchAll($sql, [$restaurantId]);
    }

    public function search($keyword, $city = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = TRUE
                AND (name LIKE ? OR description LIKE ? OR address LIKE ?)";
        
        $params = ["%{$keyword}%", "%{$keyword}%", "%{$keyword}%"];
        
        if ($city) {
            $sql .= " AND city = ?";
            $params[] = $city;
        }
        
        $sql .= " ORDER BY name";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function getStats($restaurantId)
    {
        $stats = [];
        
        // Total reservations
        $sql = "SELECT COUNT(*) as count FROM reservations WHERE restaurant_id = ?";
        $result = $this->db->fetchOne($sql, [$restaurantId]);
        $stats['total_reservations'] = $result['count'];
        
        // Pending reservations
        $sql = "SELECT COUNT(*) as count FROM reservations 
                WHERE restaurant_id = ? AND status = 'pending'";
        $result = $this->db->fetchOne($sql, [$restaurantId]);
        $stats['pending_reservations'] = $result['count'];
        
        // Today's reservations
        $sql = "SELECT COUNT(*) as count FROM reservations 
                WHERE restaurant_id = ? AND reservation_date = CURDATE()";
        $result = $this->db->fetchOne($sql, [$restaurantId]);
        $stats['today_reservations'] = $result['count'];
        
        // Total tables
        $sql = "SELECT COUNT(*) as count FROM tables WHERE restaurant_id = ?";
        $result = $this->db->fetchOne($sql, [$restaurantId]);
        $stats['total_tables'] = $result['count'];
        
        return $stats;
    }
}
