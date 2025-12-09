<?php

namespace App\Controllers;

use App\Services\Database;
use App\Services\AuthService;

class TableController extends Controller
{
    private $db;
    private $auth;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->auth = new AuthService();
    }

    /**
     * Mostrar la grilla de configuración del plano
     */
    public function plano($restaurantId)
    {
        // Verificar permisos
        if (!$this->auth->canManageRestaurant($restaurantId)) {
            $this->setFlash('error', 'No tienes permisos');
            return redirect_to('/dashboard');
        }

        $restaurant = $this->db->fetchOne(
            "SELECT * FROM restaurants WHERE id = ?",
            [$restaurantId]
        );

        if (!$restaurant) {
            $this->setFlash('error', 'Restaurante no encontrado');
            return redirect_to('/dashboard');
        }

        $tables = $this->db->fetchAll(
            "SELECT * FROM tables WHERE restaurant_id = ? ORDER BY position_y, position_x",
            [$restaurantId]
        );

        return $this->view('restaurants.plano', [
            'restaurant' => $restaurant,
            'tables' => $tables ?? []
        ]);
    }

    /**
     * Vista pública para que el cliente vea el plano y elija mesa
     */
    public function publicPlano($restaurantId)
    {
        $restaurant = $this->db->fetchOne(
            "SELECT * FROM restaurants WHERE id = ?",
            [$restaurantId]
        );

        if (!$restaurant) {
            http_response_code(404);
            return 'Restaurante no encontrado';
        }

        // Obtener todas las mesas del restaurante
        $tables = $this->db->fetchAll(
            "SELECT * FROM tables WHERE restaurant_id = ? ORDER BY position_y, position_x",
            [$restaurantId]
        );

        // Obtener IDs de mesas reservadas HOY (confirmed o pending)
        $today = date('Y-m-d');
        $reservedTableIds = $this->db->fetchAll(
            "SELECT DISTINCT rt.table_id 
             FROM reservation_tables rt
             JOIN reservations r ON rt.reservation_id = r.id
             WHERE r.restaurant_id = ? 
             AND r.reservation_date = ?
             AND r.status IN ('confirmed', 'pending')",
            [$restaurantId, $today]
        );
        
        $reservedIds = array_column($reservedTableIds, 'table_id');

        // Marcar mesas como ocupadas/disponibles
        foreach ($tables as &$table) {
            if ($table['element_type'] === 'mesa') {
                $table['is_available'] = !in_array($table['id'], $reservedIds);
            }
        }

        return $this->view('reservations.plano', [
            'restaurant' => $restaurant,
            'tables' => $tables ?? []
        ]);
    }

    /**
     * Guardar elemento en la grilla
     */
    public function savePlano($restaurantId)
    {
        header('Content-Type: application/json');

        if (!$this->auth->canManageRestaurant($restaurantId)) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            exit;
        }

        $row = (int)$this->input('row');
        $col = (int)$this->input('col');
        $elementType = $this->sanitize($this->input('element_type'));
        $zone = $this->sanitize($this->input('zone')) ?: 'General';
        $tableNumber = $this->sanitize($this->input('table_number'));
        $capacity = (int)$this->input('capacity') ?: 4;
        $connectedZone = $this->sanitize($this->input('connected_zone'));
        $description = $this->sanitize($this->input('description'));

        // Validar entrada
        if ($row < 1 || $row > 10 || $col < 1 || $col > 12) {
            echo json_encode(['success' => false, 'message' => 'Posición inválida']);
            exit;
        }

        if (!$elementType) {
            echo json_encode(['success' => false, 'message' => 'Tipo de elemento requerido']);
            exit;
        }

        // Tipos válidos
        $tiposValidos = ['mesa', 'escalera', 'bano', 'barra', 'puerta', 'pared'];
        if (!in_array($elementType, $tiposValidos)) {
            echo json_encode(['success' => false, 'message' => 'Tipo no válido']);
            exit;
        }

        try {
            // Verificar si ya existe en esa posición
            $existe = $this->db->fetchOne(
                "SELECT id FROM tables WHERE restaurant_id = ? AND position_x = ? AND position_y = ?",
                [$restaurantId, $col, $row]
            );

            // Si no hay número de mesa, generar uno basado en la posición
            if (!$tableNumber || in_array($tableNumber, $tiposValidos)) {
                $tableNumber = 'P' . $row . 'C' . $col; // P=posición, formato: P2C3
            }

            // Verificar que el número sea único para este restaurante
            $duplicado = $this->db->fetchOne(
                "SELECT id FROM tables WHERE restaurant_id = ? AND table_number = ? AND id != ?",
                [$restaurantId, $tableNumber, $existe['id'] ?? 0]
            );

            if ($duplicado) {
                // Si hay duplicado, usar la posición como nombre
                $tableNumber = 'P' . $row . 'C' . $col . '_' . time();
            }

            if ($existe) {
                // Actualizar
                $this->db->query(
                    "UPDATE tables SET element_type = ?, table_number = ?, capacity = ?, zone = ?, connected_zone = ?, description = ? WHERE id = ? AND restaurant_id = ?",
                    [$elementType, $tableNumber, $capacity, $zone, $connectedZone ?: null, $description ?: null, $existe['id'], $restaurantId]
                );
            } else {
                // Insertar nuevo
                $this->db->query(
                    "INSERT INTO tables (restaurant_id, element_type, table_number, capacity, position_x, position_y, area, floor, is_available, zone, connected_zone, description) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1, ?, ?, ?)",
                    [$restaurantId, $elementType, $tableNumber, $capacity, $col, $row, 'General', $zone, $connectedZone ?: null, $description ?: null]
                );
            }

            echo json_encode(['success' => true, 'message' => 'Guardado']);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Eliminar elemento de la grilla
     */
    public function deletePlano($restaurantId)
    {
        header('Content-Type: application/json');

        if (!$this->auth->canManageRestaurant($restaurantId)) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            exit;
        }

        $tableId = (int)$this->input('table_id');

        if (!$tableId) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }

        try {
            $this->db->query(
                "DELETE FROM tables WHERE id = ? AND restaurant_id = ?",
                [$tableId, $restaurantId]
            );

            echo json_encode(['success' => true, 'message' => 'Eliminado']);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Mover elemento en la grilla (drag & drop)
     */
    public function movePlano($restaurantId)
    {
        header('Content-Type: application/json');

        if (!$this->auth->canManageRestaurant($restaurantId)) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos']);
            exit;
        }

        $tableId = (int)$this->input('table_id');
        $newRow = (int)$this->input('row');
        $newCol = (int)$this->input('col');

        if (!$tableId || $newRow < 1 || $newRow > 10 || $newCol < 1 || $newCol > 12) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        try {
            // Verificar si la posición está ocupada
            $existing = $this->db->fetchOne(
                "SELECT id FROM tables WHERE restaurant_id = ? AND position_x = ? AND position_y = ? AND id != ?",
                [$restaurantId, $newCol, $newRow, $tableId]
            );

            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Posición ocupada']);
                exit;
            }

            // Actualizar posición
            $this->db->query(
                "UPDATE tables SET position_x = ?, position_y = ? WHERE id = ? AND restaurant_id = ?",
                [$newCol, $newRow, $tableId, $restaurantId]
            );

            echo json_encode(['success' => true, 'message' => 'Movido']);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Get tables assigned to a reservation (API)
     */
    public function getReservationTablesByReservation($reservationId)
    {
        header('Content-Type: application/json');

        try {
            $tables = $this->getReservationTables($reservationId);
            echo json_encode(['success' => true, 'table_ids' => $tables]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'table_ids' => []]);
            exit;
        }
    }

    /**
     * Helper method to get reservation tables
     */
    private function getReservationTables($reservationId)
    {
        $sql = "SELECT t.id, t.table_number, t.capacity, t.zone
                FROM reservation_tables rt
                JOIN tables t ON rt.table_id = t.id
                WHERE rt.reservation_id = ?
                ORDER BY t.position_y, t.position_x";
        return $this->db->fetchAll($sql, [$reservationId]);
    }

    /**
     * Get all plano elements for a restaurant (API)
     */
    public function getPlanoData($restaurantId)
    {
        header('Content-Type: application/json');

        try {
            $elements = $this->db->fetchAll(
                "SELECT id, element_type, position_x, position_y, table_number, capacity, zone, connected_zone 
                 FROM tables 
                 WHERE restaurant_id = ? 
                 ORDER BY position_y, position_x",
                [$restaurantId]
            );

            echo json_encode(['success' => true, 'elements' => $elements ?? []]);
            exit;
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage(), 'elements' => []]);
            exit;
        }
    }
}
