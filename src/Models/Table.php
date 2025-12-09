<?php

namespace App\Models;

class Table extends Model
{
    protected $table = 'tables';
    protected $fillable = [
        'restaurant_id', 'table_number', 'capacity', 'area', 'floor',
        'position_x', 'position_y', 'is_available', 'can_be_joined', 'joined_with_table_id'
    ];

    public function getByRestaurant($restaurantId)
    {
        return $this->where('restaurant_id = ?', [$restaurantId]);
    }

    public function getAvailableByCapacity($restaurantId, $minCapacity)
    {
        return $this->where(
            'restaurant_id = ? AND is_available = TRUE AND capacity >= ?',
            [$restaurantId, $minCapacity]
        );
    }

    public function getByArea($restaurantId, $area)
    {
        return $this->where(
            'restaurant_id = ? AND area = ?',
            [$restaurantId, $area]
        );
    }

    public function isAvailable($tableId, $date, $startTime, $endTime)
    {
        $sql = "SELECT COUNT(*) as count FROM reservations
                WHERE table_id = ?
                AND reservation_date = ?
                AND status IN ('confirmed', 'pending')
                AND (
                    (start_time < ? AND end_time > ?)
                    OR (start_time >= ? AND start_time < ?)
                )";
        
        $result = $this->db->fetchOne($sql, [
            $tableId, $date,
            $endTime, $startTime,
            $startTime, $endTime
        ]);
        
        return $result['count'] == 0;
    }

    public function getAvailableTables($restaurantId, $date, $startTime, $endTime, $guestCount)
    {
        $sql = "SELECT t.* FROM {$this->table} t
                WHERE t.restaurant_id = ?
                AND t.is_available = TRUE
                AND t.capacity >= ?
                AND t.id NOT IN (
                    SELECT table_id FROM reservations
                    WHERE restaurant_id = ?
                    AND reservation_date = ?
                    AND status IN ('confirmed', 'pending')
                    AND (
                        (start_time < ? AND end_time > ?)
                        OR (start_time >= ? AND start_time < ?)
                    )
                    AND table_id IS NOT NULL
                )
                ORDER BY t.capacity ASC";
        
        return $this->db->fetchAll($sql, [
            $restaurantId, $guestCount,
            $restaurantId, $date,
            $endTime, $startTime,
            $startTime, $endTime
        ]);
    }

    public function joinTables($tableId1, $tableId2)
    {
        $this->db->beginTransaction();
        
        try {
            // Update both tables to reference each other
            $this->update($tableId1, ['joined_with_table_id' => $tableId2]);
            $this->update($tableId2, ['joined_with_table_id' => $tableId1]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function unjoinTables($tableId)
    {
        $table = $this->find($tableId);
        
        if ($table && $table['joined_with_table_id']) {
            $this->db->beginTransaction();
            
            try {
                $joinedTableId = $table['joined_with_table_id'];
                
                $this->update($tableId, ['joined_with_table_id' => null]);
                $this->update($joinedTableId, ['joined_with_table_id' => null]);
                
                $this->db->commit();
                return true;
            } catch (\Exception $e) {
                $this->db->rollBack();
                throw $e;
            }
        }
        
        return false;
    }

    public function getJoinedTable($tableId)
    {
        $table = $this->find($tableId);
        
        if ($table && $table['joined_with_table_id']) {
            return $this->find($table['joined_with_table_id']);
        }
        
        return null;
    }

    public function getCombinedCapacity($tableId)
    {
        $table = $this->find($tableId);
        $capacity = $table['capacity'];
        
        $joinedTable = $this->getJoinedTable($tableId);
        if ($joinedTable) {
            $capacity += $joinedTable['capacity'];
        }
        
        return $capacity;
    }

    public function getAreas($restaurantId)
    {
        $sql = "SELECT DISTINCT area FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY area";
        return $this->db->fetchAll($sql, [$restaurantId]);
    }

    public function getFloors($restaurantId)
    {
        $sql = "SELECT DISTINCT floor FROM {$this->table} 
                WHERE restaurant_id = ? 
                ORDER BY floor";
        return $this->db->fetchAll($sql, [$restaurantId]);
    }
}

