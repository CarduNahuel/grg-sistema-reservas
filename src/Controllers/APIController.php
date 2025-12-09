<?php

namespace App\Controllers;

use App\Services\Database;
use App\Models\AuditLog;

class APIController extends Controller
{
    /**
     * Update order status via API
     */
    public function updateOrderStatus()
    {
        header('Content-Type: application/json');
        
        try {
            // Ensure session is started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Check authentication
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                die(json_encode(['success' => false, 'message' => 'No autenticado']));
            }

            $orderId = $_POST['order_id'] ?? null;
            $newStatus = $_POST['status'] ?? null;

            if (!$orderId || !$newStatus) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Parámetros faltantes']));
            }

            $validStatuses = ['enviado', 'en_preparacion', 'listo', 'entregado', 'cancelado'];
            if (!in_array($newStatus, $validStatuses)) {
                http_response_code(400);
                die(json_encode(['success' => false, 'message' => 'Estado inválido']));
            }

            $db = Database::getInstance();
            
            // Get current order with error checking
            $order = $db->fetchOne("SELECT id, status FROM orders WHERE id = ?", [(int)$orderId]);
            
            if (!$order) {
                http_response_code(404);
                die(json_encode(['success' => false, 'message' => 'Pedido no encontrado']));
            }

            // Prepare and execute UPDATE
            $pdo = $db->getConnection();
            $updateSql = "UPDATE orders SET status = ? WHERE id = ?";
            $stmt = $pdo->prepare($updateSql);
            
            if (!$stmt) {
                http_response_code(500);
                die(json_encode(['success' => false, 'message' => 'Error preparando query: ' . $pdo->errorInfo()[2]]));
            }
            
            $execResult = $stmt->execute([$newStatus, (int)$orderId]);
            
            if (!$execResult) {
                http_response_code(500);
                die(json_encode(['success' => false, 'message' => 'Error ejecutando query: ' . $stmt->errorInfo()[2]]));
            }
            
            $affectedRows = $stmt->rowCount();
            
            if ($affectedRows > 0) {
                // Log to audit
                $auditLog = new AuditLog();
                $auditLog->log(
                    $_SESSION['user_id'],
                    'updated_order_status',
                    'orders',
                    $orderId,
                    "Estado actualizado de '{$order['status']}' a '{$newStatus}'"
                );

                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Pedido actualizado correctamente',
                    'order_id' => $orderId,
                    'new_status' => $newStatus,
                    'old_status' => $order['status'],
                    'affected_rows' => $affectedRows
                ]);
            } else {
                // No rows affected - check if order still exists
                $verify = $db->fetchOne("SELECT id, status FROM orders WHERE id = ?", [(int)$orderId]);
                http_response_code(200);
                echo json_encode([
                    'success' => false,
                    'message' => 'No se actualizaron filas. Status actual: ' . ($verify['status'] ?? 'no existe'),
                    'affected_rows' => $affectedRows,
                    'current_status' => $verify['status'] ?? null
                ]);
            }

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        
        exit;
    }
}
