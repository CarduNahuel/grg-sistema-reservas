<?php

namespace App\Models;

class AuditLog extends Model
{
    protected $table = 'audit_log';
    protected $fillable = ['user_id', 'action', 'table_name', 'record_id', 'old_values', 'new_values', 'ip_address', 'user_agent'];

    public function log($userId, $action, $tableName, $recordId, $description = null, $oldValues = null, $newValues = null)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // Si se proporciona descripción, se guarda en el action concatenado
        if ($description) {
            $action = $action . ': ' . $description;
        }

        return $this->create([
            'user_id' => $userId,
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }

    public function getByUser($userId, $limit = 50)
    {
        return $this->where('user_id = ?', [$userId])
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }

    public function getByTable($tableName, $recordId, $limit = 50)
    {
        return $this->where('table_name = ? AND record_id = ?', [$tableName, $recordId])
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->get();
    }

    public function getRecent($limit = 100)
    {
        $sql = "SELECT al.*, u.name as user_name, u.email as user_email 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.user_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        
        $db = Database::getInstance();
        $stmt = $db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
