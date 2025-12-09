<?php

namespace App\Models;

use App\Services\Database;

class Order extends Model
{
    protected $table = 'orders';
    protected $fillable = [
        'cart_id', 'user_id', 'restaurant_id', 'reservation_id', 
        'total', 'payment_method', 'status', 'customer_phone'
    ];

    /**
     * Create order from cart
     */
    public function createFromCart($cartId, $paymentMethod = 'EFECTIVO')
    {
        $cartModel = new Cart();
        $cart = $cartModel->getCartWithDetails($cartId);
        
        if (!$cart || $cart['status'] !== 'open') {
            return false;
        }
        
        // Create order
        $orderId = $this->create([
            'cart_id' => $cartId,
            'user_id' => $cart['user_id'],
            'restaurant_id' => $cart['restaurant_id'],
            'reservation_id' => $cart['reservation_id'],
            'total' => $cart['total'],
            'payment_method' => $paymentMethod,
            'status' => 'enviado',
            'customer_phone' => $cart['customer_phone']
        ]);
        
        if (!$orderId) {
            return false;
        }
        
        // Copy cart items to order items
        foreach ($cart['items'] as $item) {
            $orderItemId = $this->db->insert(
                "INSERT INTO order_items 
                (order_id, menu_item_id, product_name, product_price, quantity, subtotal, note)
                VALUES (?, ?, ?, ?, ?, ?, ?)",
                [
                    $orderId,
                    $item['menu_item_id'],
                    $item['product_name'],
                    $item['base_price'],
                    $item['quantity'],
                    $item['total_price'],
                    $item['note'] ?? null
                ]
            );
            
            // Copy options
            if (!empty($item['options'])) {
                foreach ($item['options'] as $option) {
                    $this->db->query(
                        "INSERT INTO order_item_options 
                        (order_item_id, option_value_id, option_label, extra_price)
                        VALUES (?, ?, ?, ?)",
                        [
                            $orderItemId,
                            $option['option_value_id'],
                            $option['value_label'],
                            $option['extra_price']
                        ]
                    );
                }
            }
        }
        
        // Mark cart as sent
        $cartModel->markAsSent($cartId);
        
        return $orderId;
    }

    /**
     * Get order with full details
     */
    public function getOrderWithDetails($orderId)
    {
        $sql = "SELECT o.*, r.name as restaurant_name, r.phone as restaurant_phone,
                r.email as restaurant_email, o.customer_phone as phone,
                CONCAT(u.first_name, ' ', u.last_name) as customer_name, u.email as customer_email
                FROM {$this->table} o
                JOIN restaurants r ON o.restaurant_id = r.id
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = ?";
        
        $order = $this->db->fetchOne($sql, [$orderId]);
        
        if ($order) {
            // Get order items with aliases for compatibility
            $itemsSql = "SELECT *, product_price as base_price, subtotal as total_price 
                        FROM order_items WHERE order_id = ? ORDER BY id ASC";
            $order['items'] = $this->db->fetchAll($itemsSql, [$orderId]);
            
            // Get options for each item
            foreach ($order['items'] as &$item) {
                $optionsSql = "SELECT *, option_label as value_label, option_label as option_name 
                              FROM order_item_options WHERE order_item_id = ?";
                $item['options'] = $this->db->fetchAll($optionsSql, [$item['id']]);
            }
        }
        
        return $order;
    }

    /**
     * Get orders by restaurant
     */
    public function getByRestaurant($restaurantId, $limit = 50)
    {
        $sql = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.restaurant_id = ?
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$restaurantId, $limit]);
    }

    /**
     * Get orders by user
     */
    public function getByUser($userId, $limit = 20)
    {
        $sql = "SELECT o.*, r.name as restaurant_name,
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                FROM {$this->table} o
                JOIN restaurants r ON o.restaurant_id = r.id
                WHERE o.user_id = ?
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Get today's orders for restaurant
     */
    public function getTodayOrders($restaurantId)
    {
        $sql = "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                FROM {$this->table} o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.restaurant_id = ? AND DATE(o.created_at) = CURDATE()
                ORDER BY o.created_at DESC";
        
        return $this->db->fetchAll($sql, [$restaurantId]);
    }

    /**
     * Get order items with options
     */
    public function getOrderItems($orderId)
    {
        $itemsSql = "SELECT oi.*, 
                     mi.image_url,
                     oi.product_price as base_price,
                     oi.subtotal as total_price
                     FROM order_items oi
                     LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id
                     WHERE oi.order_id = ?
                     ORDER BY oi.id ASC";
        
        $items = $this->db->fetchAll($itemsSql, [$orderId]);
        
        // Load options for each item
        foreach ($items as &$item) {
            $optionsSql = "SELECT oio.*, 
                          mio.name as option_name,
                          oio.option_label as value_label
                          FROM order_item_options oio
                          LEFT JOIN menu_item_option_values miov ON oio.option_value_id = miov.id
                          LEFT JOIN menu_item_options mio ON miov.option_id = mio.id
                          WHERE oio.order_item_id = ?";
            $item['options'] = $this->db->fetchAll($optionsSql, [$item['id']]);
        }
        
        return $items;
    }
}
