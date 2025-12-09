<?php

namespace App\Models;

use App\Services\Database;

class MenuItemOptionValue extends Model
{
    protected $table = 'menu_item_option_values';
    protected $fillable = [
        'option_id', 'label', 'extra_price', 'is_active'
    ];

    /**
     * Get all values for an option
     */
    public function getByOption($optionId, $activeOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE option_id = ?";
        $params = [$optionId];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY extra_price ASC, label ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Toggle active status
     */
    public function toggleActive($valueId)
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        return $this->db->query($sql, [$valueId]);
    }

    /**
     * Get value with option info
     */
    public function getWithOption($valueId)
    {
        $sql = "SELECT v.*, o.name as option_name, o.menu_item_id
                FROM {$this->table} v
                JOIN menu_item_options o ON v.option_id = o.id
                WHERE v.id = ?";
        
        return $this->db->fetchOne($sql, [$valueId]);
    }
}
