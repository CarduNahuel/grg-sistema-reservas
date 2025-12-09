<?php

namespace App\Models;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'role_id', 'email', 'password', 'first_name', 
        'last_name', 'phone', 'is_active', 'email_verified_at',
        'remember_token'
    ];

    public function findByEmail($email)
    {
        return $this->first('email = ?', [$email]);
    }

    public function getRole($userId)
    {
        $sql = "SELECT r.* FROM roles r
                INNER JOIN users u ON u.role_id = r.id
                WHERE u.id = ?";
        return $this->db->fetchOne($sql, [$userId]);
    }

    public function hasRole($userId, $roleName)
    {
        $role = $this->getRole($userId);
        return $role && $role['name'] === $roleName;
    }

    public function getRestaurants($userId)
    {
        $sql = "SELECT r.*, ru.role as user_role 
                FROM restaurants r
                INNER JOIN restaurant_users ru ON ru.restaurant_id = r.id
                WHERE ru.user_id = ?
                ORDER BY r.created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function canManageRestaurant($userId, $restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM restaurant_users
                WHERE user_id = ? AND restaurant_id = ?
                AND role IN ('OWNER', 'RESTAURANT_ADMIN')";
        $result = $this->db->fetchOne($sql, [$userId, $restaurantId]);
        return $result['count'] > 0;
    }

    public function getRestaurantCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM restaurant_users
                WHERE user_id = ? AND role = 'OWNER'";
        $result = $this->db->fetchOne($sql, [$userId]);
        return (int)$result['count'];
    }

    public function createWithRole($data, $roleName)
    {
        // Get role ID
        $role = $this->db->fetchOne("SELECT id FROM roles WHERE name = ?", [$roleName]);
        if (!$role) {
            throw new \Exception("Role not found: {$roleName}");
        }

        $data['role_id'] = $role['id'];
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        return $this->create($data);
    }

    public function verifyPassword($plainPassword, $hashedPassword)
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE {$this->table} SET password = ? WHERE id = ?";
        $this->db->query($sql, [$hashedPassword, $userId]);
    }

    public function getNotifications($userId, $unreadOnly = false)
    {
        $sql = "SELECT n.* FROM notifications n
                WHERE n.user_id = ?";
        
        if ($unreadOnly) {
            $sql .= " AND n.is_read = FALSE";
        }
        
        $sql .= " ORDER BY n.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function getUnreadNotificationCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM notifications
                WHERE user_id = ? AND is_read = FALSE";
        $result = $this->db->fetchOne($sql, [$userId]);
        return (int)$result['count'];
    }
}
