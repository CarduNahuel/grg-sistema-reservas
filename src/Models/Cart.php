<?php

namespace App\Models;

use App\Services\Database;

class Cart extends Model
{
    protected $table = 'carts';
    protected $fillable = [
        'user_id', 'restaurant_id', 'reservation_id', 'customer_phone', 'status'
    ];

    /**
     * Get active cart for user
     */
    public function getActiveCart($userId, $restaurantId = null)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? AND status = 'open'";
        $params = [$userId];
        
        if ($restaurantId) {
            $sql .= " AND restaurant_id = ?";
            $params[] = $restaurantId;
        }
        
        $sql .= " ORDER BY updated_at DESC LIMIT 1";
        
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Get cart with items and restaurant info
     */
    public function getCartWithDetails($cartId)
    {
        $sql = "SELECT c.*, r.name as restaurant_name, r.phone as restaurant_phone,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name
                FROM {$this->table} c
                JOIN restaurants r ON c.restaurant_id = r.id
                LEFT JOIN users u ON c.user_id = u.id
                WHERE c.id = ?";
        
        $cart = $this->db->fetchOne($sql, [$cartId]);
        
        if ($cart) {
            // Get cart items
            $cart['items'] = $this->getCartItems($cartId);
            $cart['total'] = $this->calculateTotal($cartId);
        }
        
        return $cart;
    }

    /**
     * Get cart items with details
     */
    public function getCartItems($cartId)
    {
        $sql = "SELECT ci.*, 
                mi.name as product_name, 
                mi.image_url,
                ci.base_price
                FROM cart_items ci
                JOIN menu_items mi ON ci.menu_item_id = mi.id
                WHERE ci.cart_id = ?
                ORDER BY ci.created_at ASC";
        
        $items = $this->db->fetchAll($sql, [$cartId]);
        
        // Get options for each item
        foreach ($items as &$item) {
            $optionsSql = "SELECT cio.*, 
                          ov.label as value_label, 
                          o.name as option_name
                          FROM cart_item_options cio
                          JOIN menu_item_option_values ov ON cio.option_value_id = ov.id
                          JOIN menu_item_options o ON ov.option_id = o.id
                          WHERE cio.cart_item_id = ?";
            $item['options'] = $this->db->fetchAll($optionsSql, [$item['id']]);
        }
        
        return $items;
    }

    /**
     * Calculate cart total
     */
    public function calculateTotal($cartId)
    {
        $sql = "SELECT COALESCE(SUM(total_price), 0) as total
                FROM cart_items WHERE cart_id = ?";
        $result = $this->db->fetchOne($sql, [$cartId]);
        return $result ? (float)$result['total'] : 0.00;
    }

    /**
     * Clear cart items
     */
    public function clearCart($cartId)
    {
        $sql = "DELETE FROM cart_items WHERE cart_id = ?";
        return $this->db->query($sql, [$cartId]);
    }

    /**
     * Mark cart as sent
     */
    public function markAsSent($cartId)
    {
        return $this->update($cartId, ['status' => 'sent']);
    }

    /**
     * Get item count
     */
    public function getItemCount($cartId)
    {
        $sql = "SELECT COALESCE(SUM(quantity), 0) as count
                FROM cart_items WHERE cart_id = ?";
        $result = $this->db->fetchOne($sql, [$cartId]);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Check if cart belongs to user
     */
    public function belongsToUser($cartId, $userId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE id = ? AND user_id = ?";
        $result = $this->db->fetchOne($sql, [$cartId, $userId]);
        return $result && $result['count'] > 0;
    }

    /**
     * Link reservation to cart
     */
    public function linkReservation($cartId, $reservationId)
    {
        return $this->update($cartId, ['reservation_id' => $reservationId]);
    }
}
