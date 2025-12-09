<?php

namespace App\Models;

use App\Services\Database;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';
    protected $fillable = [
        'restaurant_id', 'name', 'description', 'is_active', 'sort_order'
    ];

    /**
     * Get all categories for a restaurant
     */
    public function getByRestaurant($restaurantId, $activeOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE restaurant_id = ?";
        $params = [$restaurantId];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY sort_order ASC, name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get category with item count
     */
    public function getWithItemCount($categoryId)
    {
        $sql = "SELECT c.*, COUNT(i.id) as item_count
                FROM {$this->table} c
                LEFT JOIN menu_items i ON c.id = i.category_id
                WHERE c.id = ?
                GROUP BY c.id";
        
        return $this->db->fetchOne($sql, [$categoryId]);
    }

    /**
     * Validate category belongs to restaurant
     */
    public function belongsToRestaurant($categoryId, $restaurantId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE id = ? AND restaurant_id = ?";
        $result = $this->db->fetchOne($sql, [$categoryId, $restaurantId]);
        return $result && $result['count'] > 0;
    }

    /**
     * Get next sort order for restaurant
     */
    public function getNextSortOrder($restaurantId)
    {
        $sql = "SELECT COALESCE(MAX(sort_order), 0) + 1 as next_order 
                FROM {$this->table} WHERE restaurant_id = ?";
        $result = $this->db->fetchOne($sql, [$restaurantId]);
        return $result ? (int)$result['next_order'] : 1;
    }

    /**
     * Toggle active status
     */
    public function toggleActive($categoryId)
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        return $this->db->query($sql, [$categoryId]);
    }

    /**
     * Update sort order
     */
    public function updateSortOrder($categoryId, $newOrder)
    {
        return $this->update($categoryId, ['sort_order' => $newOrder]);
    }
}
