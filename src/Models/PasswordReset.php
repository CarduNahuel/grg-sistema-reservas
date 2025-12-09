<?php

namespace App\Models;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $fillable = ['user_id', 'token', 'expires_at', 'used'];

    public function createToken($userId)
    {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        
        // Expires in 1 hour
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Delete old tokens for this user
        $this->db->query("DELETE FROM {$this->table} WHERE user_id = ?", [$userId]);
        
        // Create new token
        $this->create([
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        
        return $token;
    }

    public function findValidToken($token)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE token = ? 
                AND used = FALSE 
                AND expires_at > NOW()";
        return $this->db->fetchOne($sql, [$token]);
    }

    public function markAsUsed($token)
    {
        $sql = "UPDATE {$this->table} SET used = TRUE WHERE token = ?";
        $this->db->query($sql, [$token]);
    }

    public function cleanExpired()
    {
        $sql = "DELETE FROM {$this->table} WHERE expires_at < NOW()";
        $this->db->query($sql);
    }
}
