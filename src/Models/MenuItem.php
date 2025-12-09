<?php

namespace App\Models;

use App\Services\Database;

class MenuItem extends Model
{
    protected $table = 'menu_items';
    protected $fillable = [
        'category_id', 'restaurant_id', 'name', 'description', 
        'price', 'image_url', 'is_active', 'sort_order'
    ];

    /**
     * Get all items for a category
     */
    public function getByCategory($categoryId, $activeOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE category_id = ?";
        $params = [$categoryId];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get all items for a restaurant
     */
    public function getByRestaurant($restaurantId, $activeOnly = false)
    {
        $sql = "SELECT i.*, c.name as category_name
                FROM {$this->table} i
                LEFT JOIN menu_categories c ON i.category_id = c.id
                WHERE i.restaurant_id = ?";
        $params = [$restaurantId];
        
        if ($activeOnly) {
            $sql .= " AND i.is_active = 1 AND c.is_active = 1";
        }
        
        $sql .= " ORDER BY c.sort_order ASC, i.sort_order ASC, i.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get item with category and options
     */
    public function getWithDetails($itemId)
    {
        $sql = "SELECT i.*, c.name as category_name, c.restaurant_id
                FROM {$this->table} i
                LEFT JOIN menu_categories c ON i.category_id = c.id
                WHERE i.id = ?";
        
        $item = $this->db->fetchOne($sql, [$itemId]);
        
        if ($item) {
            // Get options for this item
            $optionsSql = "SELECT o.*, 
                          (SELECT COUNT(*) FROM menu_item_option_values WHERE option_id = o.id AND is_active = 1) as value_count
                          FROM menu_item_options o
                          WHERE o.menu_item_id = ? AND o.is_active = 1
                          ORDER BY o.id ASC";
            $item['options'] = $this->db->fetchAll($optionsSql, [$itemId]);
            
            // Get option values for each option
            foreach ($item['options'] as &$option) {
                $valuesSql = "SELECT * FROM menu_item_option_values 
                             WHERE option_id = ? AND is_active = 1
                             ORDER BY extra_price ASC, label ASC";
                $option['values'] = $this->db->fetchAll($valuesSql, [$option['id']]);
            }
        }
        
        return $item;
    }

    /**
     * Validate item belongs to restaurant
     */
    public function belongsToRestaurant($itemId, $restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->fetchOne($sql, [$itemId, $restaurantId]);
        return $result && $result['count'] > 0;
    }

    /**
     * Get next sort order for category
     */
    public function getNextSortOrder($categoryId)
    {
        $sql = "SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order 
                FROM {$this->table} WHERE category_id = ?";
        $result = $this->db->fetchOne($sql, [$categoryId]);
        return $result ? (int)$result['next_order'] : 1;
    }

    /**
     * Toggle active status
     */
    public function toggleActive($itemId)
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        return $this->db->query($sql, [$itemId]);
    }

    /**
     * Update sort order
     */
    public function updateSortOrder($itemId, $newOrder)
    {
        return $this->update($itemId, ['sort_order' => $newOrder]);
    }

    /**
     * Search items by name
     */
    public function search($restaurantId, $query, $activeOnly = true)
    {
        $sql = "SELECT i.*, c.name as category_name
                FROM {$this->table} i
                LEFT JOIN menu_categories c ON i.category_id = c.id
                WHERE i.restaurant_id = ? AND i.name LIKE ?";
        $params = [$restaurantId, "%{$query}%"];
        
        if ($activeOnly) {
            $sql .= " AND i.is_active = 1";
        }
        
        $sql .= " ORDER BY i.name ASC LIMIT 20";
        
        return $this->db->fetchAll($sql, $params);
    }
}
