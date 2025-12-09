<?php

namespace App\Models;

use App\Services\Database;

class MenuItemOption extends Model
{
    protected $table = 'menu_item_options';
    protected $fillable = [
        'menu_item_id', 'name', 'extra_price', 'is_required', 'is_active'
    ];

    /**
     * Get all options for a menu item
     */
    public function getByMenuItem($menuItemId, $activeOnly = false)
    {
        $sql = "SELECT * FROM {$this->table} WHERE menu_item_id = ?";
        $params = [$menuItemId];
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY id ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get option with values
     */
    public function getWithValues($optionId)
    {
        $option = $this->find($optionId);
        
        if ($option) {
            $sql = "SELECT * FROM menu_item_option_values 
                    WHERE option_id = ? 
                    ORDER BY extra_price ASC, label ASC";
            $option['values'] = $this->db->fetchAll($sql, [$optionId]);
        }
        
        return $option;
    }

    /**
     * Toggle active status
     */
    public function toggleActive($optionId)
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = ?";
        return $this->db->query($sql, [$optionId]);
    }

    /**
     * Delete option and its values
     */
    public function deleteWithValues($optionId)
    {
        // Cascade will handle values automatically
        return $this->delete($optionId);
    }
}
