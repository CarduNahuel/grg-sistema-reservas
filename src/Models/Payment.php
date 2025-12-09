<?php

namespace App\Models;

class Payment extends Model
{
    protected $table = 'payments';
    protected $fillable = [
        'user_id', 'restaurant_id', 'amount', 'currency', 'status',
        'payment_method', 'transaction_id', 'gateway_response', 'paid_at'
    ];

    public function getByUser($userId)
    {
        $sql = "SELECT p.*, r.name as restaurant_name
                FROM {$this->table} p
                INNER JOIN restaurants r ON p.restaurant_id = r.id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function getByRestaurant($restaurantId)
    {
        return $this->where('restaurant_id = ?', [$restaurantId]);
    }

    public function getPending($userId = null)
    {
        $sql = "SELECT p.*, r.name as restaurant_name, u.email, u.first_name, u.last_name
                FROM {$this->table} p
                INNER JOIN restaurants r ON p.restaurant_id = r.id
                INNER JOIN users u ON p.user_id = u.id
                WHERE p.status = 'pending'";
        
        $params = [];
        
        if ($userId) {
            $sql .= " AND p.user_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    public function markAsPaid($paymentId, $transactionId, $gatewayResponse = null)
    {
        return $this->update($paymentId, [
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'gateway_response' => $gatewayResponse ? json_encode($gatewayResponse) : null,
            'paid_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markAsFailed($paymentId, $gatewayResponse = null)
    {
        return $this->update($paymentId, [
            'status' => 'failed',
            'gateway_response' => $gatewayResponse ? json_encode($gatewayResponse) : null
        ]);
    }

    public function createForRestaurant($userId, $restaurantId, $amount)
    {
        return $this->create([
            'user_id' => $userId,
            'restaurant_id' => $restaurantId,
            'amount' => $amount,
            'currency' => 'USD',
            'status' => 'pending'
        ]);
    }

    public function hasSuccessfulPayment($restaurantId)
    {
        $count = $this->count(
            'restaurant_id = ? AND status = ?',
            [$restaurantId, 'completed']
        );
        return $count > 0;
    }

    public function getTotal($userId = null, $status = null)
    {
        $sql = "SELECT SUM(amount) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($userId) {
            $sql .= " AND user_id = ?";
            $params[] = $userId;
        }
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return (float)($result['total'] ?? 0);
    }
}
