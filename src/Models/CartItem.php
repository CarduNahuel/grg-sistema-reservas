<?php

namespace App\Models;

use App\Services\Database;

class CartItem extends Model
{
    protected $table = 'cart_items';
    protected $fillable = [
        'cart_id', 'menu_item_id', 'quantity', 'note', 'base_price', 'total_price'
    ];

    /**
     * Add item to cart
     */
    public function addToCart($cartId, $menuItemId, $quantity, $note, $basePrice, $options = [])
    {
        // Calculate total with options
        $optionsPrice = 0;
        foreach ($options as $optionValueId => $extraPrice) {
            $optionsPrice += (float)$extraPrice;
        }
        
        $totalPrice = ($basePrice + $optionsPrice) * $quantity;
        
        // Insert cart item
        $cartItemId = $this->create([
            'cart_id' => $cartId,
            'menu_item_id' => $menuItemId,
            'quantity' => $quantity,
            'note' => $note,
            'base_price' => $basePrice,
            'total_price' => $totalPrice
        ]);
        
        // Insert cart item options
        if ($cartItemId && !empty($options)) {
            foreach ($options as $optionValueId => $extraPrice) {
                $sql = "INSERT INTO cart_item_options 
                        (cart_item_id, option_value_id, extra_price) 
                        VALUES (?, ?, ?)";
                $this->db->query($sql, [$cartItemId, $optionValueId, $extraPrice]);
            }
        }
        
        return $cartItemId;
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity($cartItemId, $newQuantity)
    {
        // Get current item
        $item = $this->find($cartItemId);
        if (!$item) {
            return false;
        }
        
        // Recalculate total
        $baseWithOptions = (float)$item['base_price'];
        
        // Add options price
        $sql = "SELECT COALESCE(SUM(extra_price), 0) as options_total 
                FROM cart_item_options WHERE cart_item_id = ?";
        $result = $this->db->fetchOne($sql, [$cartItemId]);
        $baseWithOptions += (float)$result['options_total'];
        
        $newTotal = $baseWithOptions * $newQuantity;
        
        return $this->update($cartItemId, [
            'quantity' => $newQuantity,
            'total_price' => $newTotal
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($cartItemId)
    {
        // Cascade will remove options automatically
        return $this->delete($cartItemId);
    }

    /**
     * Get items by cart
     */
    public function getByCart($cartId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE cart_id = ? ORDER BY created_at ASC";
        return $this->db->fetchAll($sql, [$cartId]);
    }
}
