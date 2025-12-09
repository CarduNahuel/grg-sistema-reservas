<?php

namespace App\Models;

use App\Services\Database;
use PDO;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    public function where($conditions, $params = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions}";
        return $this->db->fetchAll($sql, $params);
    }

    public function first($conditions, $params = [])
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$conditions} LIMIT 1";
        return $this->db->fetchOne($sql, $params);
    }

    public function create($data)
    {
        $data = $this->filterFillable($data);
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        return $this->db->insert($sql, array_values($data));
    }

    public function update($id, $data)
    {
        $data = $this->filterFillable($data);
        
        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = "{$column} = ?";
        }
        $setClause = implode(', ', $sets);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $this->db->query($sql, $params);
        return $this->find($id);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
        return true;
    }

    public function count($conditions = '1=1', $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$conditions}";
        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }

    protected function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }

    public function fetchOne($sql, $params = [])
    {
        return $this->db->fetchOne($sql, $params);
    }
}
