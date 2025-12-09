<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\AuditLog;
use App\Models\PasswordReset;
use App\Models\Notification;
use App\Services\Database;

class AdminController extends Controller
{
    private $userModel;
    private $roleModel;
    private $auditLog;
    private $notificationModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->roleModel = new Role();
        $this->auditLog = new AuditLog();
        $this->notificationModel = new Notification();

        // Verificar que el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /grg/auth/login');
            exit;
        }

        // Verificar que el usuario es SUPERADMIN
        $role = $this->userModel->getRole($_SESSION['user_id']);
        if (!$role || $role['name'] !== 'SUPERADMIN') {
            $this->setFlash('error', 'No tienes permisos para acceder a esta sección.');
            header('Location: /grg/dashboard');
            exit;
        }
    }

    public function users()
    {
        $search = $_GET['search'] ?? '';
        $roleFilter = $_GET['role'] ?? '';
        $statusFilter = $_GET['status'] ?? '';

        $db = Database::getInstance();
        
        $query = "SELECT u.*, r.name as role_name, CONCAT(u.first_name, ' ', u.last_name) as name 
              FROM users u 
              JOIN roles r ON u.role_id = r.id 
              WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($roleFilter)) {
            $query .= " AND u.role_id = ?";
            $params[] = $roleFilter;
        }

        if ($statusFilter !== '') {
            $query .= " AND u.is_active = ?";
            $params[] = (int)$statusFilter;
        }

        $query .= " ORDER BY u.created_at DESC";

        $users = $db->fetchAll($query, $params);

        // Obtener todos los roles para el filtro
        $roles = $this->roleModel->all();
        
        // Obtener usuario actual
        $currentUser = $this->userModel->find($_SESSION['user_id']);

        return $this->view('admin.users', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'statusFilter' => $statusFilter,
            'currentUser' => $currentUser
        ]);
    }

    public function toggleActive()
    {
        $userId = $this->input('user_id');
        $currentStatus = $this->input('current_status');

        if (empty($userId)) {
            $this->setFlash('error', 'Usuario no especificado.');
            return $this->back();
        }

        // No permitir desactivar al propio usuario
        if ($userId == $_SESSION['user']['id']) {
            $this->setFlash('error', 'No puedes desactivar tu propia cuenta.');
            return $this->back();
        }

        $newStatus = $currentStatus ? 0 : 1;
        
        $db = Database::getInstance();
        $db->query("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $userId]);

        // Registrar en audit log
        $action = $newStatus ? 'activated' : 'deactivated';
        $this->auditLog->log(
            $_SESSION['user']['id'],
            $action . '_user',
            'users',
            $userId,
            "Usuario {$action}"
        );

        $message = $newStatus ? 'Usuario activado exitosamente.' : 'Usuario desactivado exitosamente.';
        $this->setFlash('success', $message);

        return $this->back();
    }

    public function changeRole()
    {
        $userId = $this->input('user_id');
        $newRoleId = $this->input('role_id');

        if (empty($userId) || empty($newRoleId)) {
            $this->setFlash('error', 'Datos incompletos.');
            return $this->back();
        }

        // No permitir cambiar el rol del propio usuario
        if ($userId == $_SESSION['user']['id']) {
            $this->setFlash('error', 'No puedes cambiar tu propio rol.');
            return $this->back();
        }

        // Obtener el rol anterior
        $user = $this->userModel->find($userId);
        $oldRoleId = $user['role_id'];

        $db = Database::getInstance();
        $db->query("UPDATE users SET role_id = ? WHERE id = ?", [$newRoleId, $userId]);

        // Registrar en audit log
        $this->auditLog->log(
            $_SESSION['user']['id'],
            'changed_user_role',
            'users',
            $userId,
            "Rol cambiado de {$oldRoleId} a {$newRoleId}"
        );

        $this->setFlash('success', 'Rol actualizado exitosamente.');
        return $this->back();
    }

    public function resetUserPassword()
    {
        $userId = $this->input('user_id');

        if (empty($userId)) {
            $this->setFlash('error', 'Usuario no especificado.');
            return $this->back();
        }

        $user = $this->userModel->find($userId);

        if (!$user) {
            $this->setFlash('error', 'Usuario no encontrado.');
            return $this->back();
        }

        // Crear token de recuperación
        $passwordReset = new PasswordReset();
        $token = $passwordReset->createToken($userId);

        // Enviar email (igual que en AuthController)
        $this->sendResetEmail($user['email'], $token, $user['name']);

        // Registrar en audit log
        $this->auditLog->log(
            $_SESSION['user']['id'],
            'admin_password_reset',
            'users',
            $userId,
            "Admin solicitó reset de contraseña para usuario"
        );

        $this->setFlash('success', 'Se ha enviado un enlace de recuperación al usuario.');
        return $this->back();
    }

    public function viewUserHistory($userId)
    {
        if (empty($userId)) {
            $this->setFlash('error', 'Usuario no especificado.');
            return redirect_to('/admin/users');
        }

        $user = $this->userModel->find($userId);

        if (!$user) {
            $this->setFlash('error', 'Usuario no encontrado.');
            return redirect_to('/admin/users');
        }

        // Obtener historial de auditoría del usuario
        $db = Database::getInstance();
        $auditHistory = $db->fetchAll(
            "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) as admin_name 
             FROM audit_log al 
             LEFT JOIN users u ON al.user_id = u.id 
             WHERE al.record_id = ? AND al.table_name = 'users'
             ORDER BY al.created_at DESC",
            [$userId]
        );

        // Obtener reservas del usuario
        $reservations = $db->fetchAll(
            "SELECT r.*, res.name as restaurant_name, res.image_url 
             FROM reservations r 
             JOIN restaurants res ON r.restaurant_id = res.id 
             WHERE r.user_id = ? 
             ORDER BY r.reservation_date DESC, r.start_time DESC 
             LIMIT 20",
            [$userId]
        );

        return $this->view('admin.user-history', [
            'user' => $user,
            'auditHistory' => $auditHistory,
            'reservations' => $reservations
        ]);
    }

    private function sendResetEmail($email, $token, $name)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
            $mail->Port = $_ENV['MAIL_PORT'];
            $mail->CharSet = 'UTF-8';

            // Destinatarios
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email, $name);

            // Contenido
            $resetLink = "http://localhost/grg/auth/reset-password/{$token}";
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña - GRG (Admin)';
            $mail->Body = "
                <h2>Recuperación de Contraseña</h2>
                <p>Hola {$name},</p>
                <p>Un administrador ha solicitado restablecer tu contraseña en GRG.</p>
                <p>Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                <p><a href='{$resetLink}' style='background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Restablecer Contraseña</a></p>
                <p>Este enlace expirará en 1 hora.</p>
                <p>Si no solicitaste este cambio, comunícate con el administrador del sistema.</p>
                <hr>
                <p style='color: #666; font-size: 12px;'>GRG - Gestor de Reservas Gastronómicas</p>
            ";

            $mail->send();
        } catch (\Exception $e) {
            error_log("Error enviando email de recuperación: {$mail->ErrorInfo}");
        }
    }

    // Gestión de Reservas
    public function reservations()
    {
        $search = $_GET['search'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        $restaurantFilter = $_GET['restaurant'] ?? '';

        $db = Database::getInstance();
        
        $query = "SELECT r.*, res.name as restaurant_name, u.email as user_email, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name
                  FROM reservations r 
                  JOIN restaurants res ON r.restaurant_id = res.id 
                  JOIN users u ON r.user_id = u.id 
                  WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (u.email LIKE ? OR res.name LIKE ? OR r.id LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($statusFilter)) {
            $query .= " AND r.status = ?";
            $params[] = $statusFilter;
        }

        if (!empty($restaurantFilter)) {
            $query .= " AND r.restaurant_id = ?";
            $params[] = $restaurantFilter;
        }

        $query .= " ORDER BY r.reservation_date DESC, r.start_time DESC";

        $reservations = $db->fetchAll($query, $params);

        // Para cada reserva, cargar elementos del plano y mesas asignadas
        foreach ($reservations as &$reservation) {
            // Cargar elementos del plano del restaurante
            $reservation['plano_elements'] = $db->fetchAll(
                "SELECT id, element_type, position_x, position_y, table_number, capacity, zone, connected_zone 
                 FROM tables 
                 WHERE restaurant_id = ? 
                 ORDER BY position_y, position_x",
                [$reservation['restaurant_id']]
            );

            // Cargar mesas asignadas a esta reserva
            $reservation['assigned_tables'] = $db->fetchAll(
                "SELECT t.id, t.table_number, t.capacity, t.zone
                 FROM reservation_tables rt
                 JOIN tables t ON rt.table_id = t.id
                 WHERE rt.reservation_id = ?
                 ORDER BY t.position_y, t.position_x",
                [$reservation['id']]
            );

            // Cargar mesas OCUPADAS en la misma fecha (por otras reservas)
            $reservation['occupied_tables'] = $db->fetchAll(
                "SELECT DISTINCT t.id, t.table_number
                 FROM reservation_tables rt
                 JOIN tables t ON rt.table_id = t.id
                 JOIN reservations r ON rt.reservation_id = r.id
                 WHERE r.reservation_date = ?
                 AND r.restaurant_id = ?
                 AND r.status IN ('confirmed', 'pending')
                 AND r.id != ?",
                [$reservation['reservation_date'], $reservation['restaurant_id'], $reservation['id']]
            );
        }

        // Obtener restaurantes para filtro
        $restaurants = $db->fetchAll("SELECT id, name FROM restaurants ORDER BY name");

        return $this->view('admin.reservations', [
            'reservations' => $reservations,
            'restaurants' => $restaurants,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'restaurantFilter' => $restaurantFilter
        ]);
    }

    public function updateReservationStatus()
    {
        $reservationId = $this->input('reservation_id');
        $newStatus = $this->input('status');
        $tableIdsRaw = $this->input('table_ids');
        $tableIds = array_filter(array_map('intval', explode(',', (string)$tableIdsRaw)));
        $primaryTableId = $tableIds[0] ?? null;

        if (empty($reservationId) || empty($newStatus)) {
            $this->setFlash('error', 'Datos incompletos.');
            return $this->back();
        }

        $validStatuses = ['pending', 'confirmed', 'rejected', 'completed', 'cancelled', 'no_show'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->setFlash('error', 'Estado de reserva inválido.');
            return $this->back();
        }

        $db = Database::getInstance();
        
        try {
            // Obtener info de la reserva
            $reservation = $db->fetchOne("SELECT * FROM reservations WHERE id = ?", [$reservationId]);
            if (!$reservation) {
                $this->setFlash('error', 'Reserva no encontrada.');
                return $this->back();
            }

            if ($newStatus === 'confirmed' && !empty($tableIds)) {
                // Verificar disponibilidad de mesas para esta fecha
                $reservationDate = $reservation['reservation_date'];
                $conflictingReservations = $db->fetchAll(
                    "SELECT DISTINCT rt.table_id, r.id as reservation_id
                     FROM reservation_tables rt
                     JOIN reservations r ON rt.reservation_id = r.id
                     WHERE r.reservation_date = ?
                     AND r.status IN ('confirmed', 'pending')
                     AND r.id != ?
                     AND rt.table_id IN (" . implode(',', array_fill(0, count($tableIds), '?')) . ")",
                    array_merge([$reservationDate, $reservationId], $tableIds)
                );

                if (!empty($conflictingReservations)) {
                    $conflictingTableIds = array_column($conflictingReservations, 'table_id');
                    $this->setFlash('error', 'Las mesas ' . implode(', ', $conflictingTableIds) . ' ya están reservadas para esta fecha.');
                    return $this->back();
                }

                // Si pasa validación, guardar
                $db->query("UPDATE reservations SET status = ?, table_id = ? WHERE id = ?", [$newStatus, $primaryTableId, $reservationId]);
                $db->query("DELETE FROM reservation_tables WHERE reservation_id = ?", [$reservationId]);
                
                foreach ($tableIds as $tid) {
                    $db->query("INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)", [$reservationId, $tid]);
                }
                
                $logMessage = "Estado actualizado a: {$newStatus} | Mesas asignadas: " . implode(',', $tableIds);
                $this->setFlash('success', 'Reserva confirmada con ' . count($tableIds) . ' mesa(s) asignada(s).');
            } else {
                // Solo cambiar estado
                $db->query("UPDATE reservations SET status = ? WHERE id = ?", [$newStatus, $reservationId]);
                $logMessage = "Estado de reserva actualizado a: {$newStatus}";
                $this->setFlash('success', 'Estado de reserva actualizado a ' . $newStatus . '.');
            }

            // Registrar en audit log
            $this->auditLog->log(
                $_SESSION['user_id'],
                'updated_reservation_status',
                'reservations',
                $reservationId,
                $logMessage
            );

            return $this->back();
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al guardar: ' . $e->getMessage());
            return $this->back();
        }
    }

    public function notifyReservation()
    {
        $reservationId = $this->input('reservation_id');

        if (empty($reservationId)) {
            $this->setFlash('error', 'Reserva no especificada.');
            return $this->back();
        }

        $db = Database::getInstance();
        $reservation = $db->fetchOne(
            "SELECT r.*, u.email as user_email, CONCAT(u.first_name, ' ', u.last_name) as user_name,
             res.name as restaurant_name
             FROM reservations r 
             JOIN users u ON r.user_id = u.id 
             JOIN restaurants res ON r.restaurant_id = res.id 
             WHERE r.id = ?",
            [$reservationId]
        );

        if (!$reservation) {
            $this->setFlash('error', 'Reserva no encontrada.');
            return $this->back();
        }

        // Crear notificación interna
        $statusName = [
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'rejected' => 'Rechazada',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            'no_show' => 'No Show'
        ][$reservation['status']] ?? $reservation['status'];

        $reservationDate = $reservation['reservation_date'] && $reservation['reservation_date'] !== '0000-00-00'
            ? date('d/m/Y', strtotime($reservation['reservation_date']))
            : date('d/m/Y', strtotime($reservation['start_time']));
        $reservationTime = date('H:i', strtotime($reservation['start_time']));

        $title = 'Actualización de reserva';
        $message = "Tu reserva en {$reservation['restaurant_name']} fue {$statusName} para el {$reservationDate} a las {$reservationTime}.";
        $notificationId = $this->notificationModel->createReservationNotification(
            'reservation_status',
            $reservation['user_id'],
            $reservationId,
            $title,
            $message
        );

        // Enviar notificación por email
        $this->sendReservationNotification($reservation);

        // Registrar en audit log
        $this->auditLog->log(
            $_SESSION['user_id'],
            'notified_reservation',
            'reservations',
            $reservationId,
            "Notificación enviada al usuario sobre su reserva"
        );

        $this->setFlash('success', 'Notificación enviada al usuario.');
        return $this->back();
    }

    // Gestión de Restaurantes
    public function restaurantsAdmin()
    {
        $search = $_GET['search'] ?? '';
        $statusFilter = $_GET['status'] ?? '';

        $db = Database::getInstance();
        
        $query = "SELECT r.*, COUNT(DISTINCT t.id) as table_count, 
                  CONCAT(u.first_name, ' ', u.last_name) as owner_name
                  FROM restaurants r 
                  LEFT JOIN tables t ON r.id = t.restaurant_id 
                  LEFT JOIN users u ON r.owner_id = u.id 
                  WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (r.name LIKE ? OR r.email LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($statusFilter !== '') {
            $query .= " AND r.is_active = ?";
            $params[] = (int)$statusFilter;
        }

        $query .= " GROUP BY r.id ORDER BY r.created_at DESC";

        $restaurants = $db->fetchAll($query, $params);

        return $this->view('admin.restaurants', [
            'restaurants' => $restaurants,
            'search' => $search,
            'statusFilter' => $statusFilter
        ]);
    }

    public function toggleRestaurantActive()
    {
        $restaurantId = $this->input('restaurant_id');
        $currentStatus = $this->input('current_status');

        if (empty($restaurantId)) {
            $this->setFlash('error', 'Restaurante no especificado.');
            return $this->back();
        }

        $newStatus = $currentStatus ? 0 : 1;
        
        $db = Database::getInstance();
        $db->query("UPDATE restaurants SET is_active = ? WHERE id = ?", [$newStatus, $restaurantId]);

        // Registrar en audit log
        $action = $newStatus ? 'activated' : 'deactivated';
        $this->auditLog->log(
            $_SESSION['user_id'],
            $action . '_restaurant',
            'restaurants',
            $restaurantId,
            "Restaurante {$action}"
        );

        $message = $newStatus ? 'Restaurante activado.' : 'Restaurante desactivado.';
        $this->setFlash('success', $message);
        return $this->back();
    }

    private function sendReservationNotification($reservation)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
            $mail->Port = $_ENV['MAIL_PORT'];
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($reservation['user_email'], $reservation['user_name']);

            $statusName = [
                'pending' => 'Pendiente',
                'confirmed' => 'Confirmada',
                'rejected' => 'Rechazada',
                'completed' => 'Completada',
                'cancelled' => 'Cancelada',
                'no_show' => 'No Show'
            ][$reservation['status']] ?? $reservation['status'];

            $reservationDate = date('d/m/Y', strtotime($reservation['reservation_date']));
            $reservationTime = date('H:i', strtotime($reservation['start_time']));

            $mail->isHTML(true);
            $mail->Subject = 'Notificación de Reserva - GRG';
            $mail->Body = "
                <h2>Notificación de Reserva</h2>
                <p>Hola {$reservation['user_name']},</p>
                <p>Te escribimos para notificarte sobre tu reserva en <strong>{$reservation['restaurant_name']}</strong>.</p>
                <hr>
                <h4>Detalles de la Reserva:</h4>
                <ul>
                    <li><strong>Restaurante:</strong> {$reservation['restaurant_name']}</li>
                    <li><strong>Fecha:</strong> {$reservationDate}</li>
                    <li><strong>Hora:</strong> {$reservationTime}</li>
                    <li><strong>Cantidad de Personas:</strong> {$reservation['guest_count']}</li>
                    <li><strong>Estado:</strong> <strong>{$statusName}</strong></li>
                </ul>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <hr>
                <p style='color: #666; font-size: 12px;'>GRG - Gestor de Reservas Gastronómicas</p>
            ";

            $mail->send();
        } catch (\Exception $e) {
            error_log("Error enviando notificación de reserva: {$mail->ErrorInfo}");
        }
    }

    public function editRestaurant($restaurantId)
    {
        if (empty($restaurantId)) {
            $this->setFlash('error', 'Restaurante no especificado.');
            return redirect_to('/admin/restaurants');
        }

        $db = Database::getInstance();
        $restaurant = $db->fetchOne(
            "SELECT r.* FROM restaurants r WHERE r.id = ?",
            [$restaurantId]
        );

        if (!$restaurant) {
            $this->setFlash('error', 'Restaurante no encontrado.');
            return redirect_to('/admin/restaurants');
        }

        return $this->view('admin.edit-restaurant', ['restaurant' => $restaurant]);
    }

    public function updateRestaurant($restaurantId)
    {
        $name = $this->sanitize($this->input('name'));
        $description = $this->sanitize($this->input('description'));
        $email = $this->sanitize($this->input('email'));
        $phone = $this->sanitize($this->input('phone'));
        $address = $this->sanitize($this->input('address'));
        $city = $this->sanitize($this->input('city'));
        $state = $this->sanitize($this->input('state'));
        $postalCode = $this->sanitize($this->input('postal_code'));
        $openingTime = $this->input('opening_time');
        $closingTime = $this->input('closing_time');
        $requiresPayment = $this->input('requires_payment') ? 1 : 0;

        // Validaciones
        if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city)) {
            $this->setFlash('error', 'Por favor completa todos los campos requeridos.');
            return $this->back();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'El email no es válido.');
            return $this->back();
        }

        $db = Database::getInstance();
        $db->query(
            "UPDATE restaurants SET name = ?, description = ?, email = ?, phone = ?, 
             address = ?, city = ?, state = ?, postal_code = ?, 
             opening_time = ?, closing_time = ?, requires_payment = ? WHERE id = ?",
            [$name, $description, $email, $phone, $address, $city, $state, $postalCode, 
             $openingTime, $closingTime, $requiresPayment, $restaurantId]
        );

        // Registrar en audit log
        $this->auditLog->log(
            $_SESSION['user_id'],
            'updated_restaurant',
            'restaurants',
            $restaurantId,
            "Restaurante actualizado por admin"
        );

        $this->setFlash('success', 'Restaurante actualizado exitosamente.');
        return redirect_to('/admin/restaurants');
    }

    public function restaurantTables($restaurantId)
    {
        if (empty($restaurantId)) {
            $this->setFlash('error', 'Restaurante no especificado.');
            return redirect_to('/admin/restaurants');
        }

        $db = Database::getInstance();
        $restaurant = $db->fetchOne(
            "SELECT * FROM restaurants WHERE id = ?",
            [$restaurantId]
        );

        if (!$restaurant) {
            $this->setFlash('error', 'Restaurante no encontrado.');
            return redirect_to('/admin/restaurants');
        }

        $selectedArea = $this->sanitize($this->input('area')) ?: null;

        $tables = $db->fetchAll(
            "SELECT t.*, 
                    EXISTS(
                        SELECT 1 FROM reservations r 
                        WHERE r.table_id = t.id 
                          AND r.status IN ('pending','confirmed') 
                          AND r.start_time >= NOW()
                    ) AS has_active_reservation,
                    (
                        SELECT guest_count FROM reservations r 
                        WHERE r.table_id = t.id 
                          AND r.status IN ('pending','confirmed') 
                          AND r.start_time >= NOW()
                        ORDER BY r.start_time ASC LIMIT 1
                    ) AS next_guest_count
             FROM tables t 
             WHERE t.restaurant_id = ?
             ORDER BY t.area, t.table_number",
            [$restaurantId]
        );

        // Construir lista de áreas (grillas)
        $areas = [];
        foreach ($tables as $t) {
            $areas[] = $t['area'] ?: 'General';
        }
        $areas = array_values(array_unique($areas));

        if (!$selectedArea && !empty($areas)) {
            $selectedArea = $areas[0];
        }

        $tablesForArea = array_filter($tables, function ($t) use ($selectedArea) {
            $areaName = $t['area'] ?: 'General';
            return $selectedArea ? $areaName === $selectedArea : true;
        });

        // Redirigir al propietario para que use la vista del plano
        $this->setFlash('info', 'Usa la página de configuración del plano del restaurante.');
        return redirect_to('/owner/restaurants/' . $restaurantId . '/plano');
    }

    public function saveTableLayout($restaurantId)
    {
        $area = $this->sanitize($this->input('area')) ?: 'General';
        $layoutJson = $this->input('layout');
        $layout = json_decode($layoutJson, true);

        if (!is_array($layout)) {
            $this->setFlash('error', 'No se recibió un layout válido.');
            return $this->back();
        }

        $db = Database::getInstance();

        foreach ($layout as $item) {
            $tableId = isset($item['id']) ? (int)$item['id'] : 0;
            $col = isset($item['col']) ? (int)$item['col'] : null;
            $row = isset($item['row']) ? (int)$item['row'] : null;

            if (empty($tableId) || $col === null || $row === null) {
                continue;
            }

            // Verificar pertenencia
            $exists = $db->fetchOne(
                "SELECT id FROM tables WHERE id = ? AND restaurant_id = ?",
                [$tableId, $restaurantId]
            );

            if (!$exists) {
                continue;
            }

            $db->query(
                "UPDATE tables SET position_x = ?, position_y = ?, area = ? WHERE id = ? AND restaurant_id = ?",
                [$col, $row, $area, $tableId, $restaurantId]
            );
        }

        $this->setFlash('success', 'Layout guardado correctamente.');
        return redirect_to('/admin/restaurants/' . $restaurantId . '/tables?area=' . urlencode($area));
    }

    public function resetTables($restaurantId)
    {
        $db = Database::getInstance();

        // Borrar mesas actuales del restaurante
        $db->query("DELETE FROM tables WHERE restaurant_id = ?", [$restaurantId]);

        // Layout demo (12x10). Ajusta según prefieras.
        $seed = [
            ['table_number' => 'A1', 'capacity' => 4, 'area' => 'Interior', 'col' => 3,  'row' => 3],
            ['table_number' => 'A2', 'capacity' => 4, 'area' => 'Interior', 'col' => 5,  'row' => 3],
            ['table_number' => 'A3', 'capacity' => 4, 'area' => 'Interior', 'col' => 7,  'row' => 3],
            ['table_number' => 'B1', 'capacity' => 2, 'area' => 'Interior', 'col' => 3,  'row' => 6],
            ['table_number' => 'B2', 'capacity' => 2, 'area' => 'Interior', 'col' => 5,  'row' => 6],
            ['table_number' => 'B3', 'capacity' => 2, 'area' => 'Interior', 'col' => 7,  'row' => 6],
            ['table_number' => 'Ter-1', 'capacity' => 6, 'area' => 'Terraza', 'col' => 4, 'row' => 4],
            ['table_number' => 'Ter-2', 'capacity' => 6, 'area' => 'Terraza', 'col' => 6, 'row' => 7],
        ];

        foreach ($seed as $t) {
            $db->query(
                "INSERT INTO tables (restaurant_id, table_number, capacity, area, floor, position_x, position_y, is_available, can_be_joined) 
                 VALUES (?, ?, ?, ?, 1, ?, ?, 1, 0)",
                [$restaurantId, $t['table_number'], $t['capacity'], $t['area'], $t['col'], $t['row']]
            );
        }

        $this->auditLog->log(
            $_SESSION['user_id'],
            'reset_tables_layout',
            'tables',
            $restaurantId,
            "Mesas reseteadas y recreadas en layout demo"
        );

        $this->setFlash('success', 'Se reiniciaron las mesas con un layout demo.');
        return redirect_to('/admin/restaurants/' . $restaurantId . '/tables');
    }

    public function addTable($restaurantId)
    {
        $tableNumber = $this->sanitize($this->input('table_number'));
        $capacity = (int)$this->input('capacity');
        $area = $this->sanitize($this->input('area'));
        $floor = (int)$this->input('floor') ?: 1;
        $isAvailable = $this->input('is_available') ? 1 : 0;
        $canBeJoined = $this->input('can_be_joined') ? 1 : 0;

        // Validaciones
        if (empty($tableNumber) || $capacity < 1) {
            $this->setFlash('error', 'Por favor completa todos los campos requeridos.');
            return $this->back();
        }

        $db = Database::getInstance();

        // Verificar que no exista una mesa con el mismo número en este restaurante
        $existing = $db->fetchOne(
            "SELECT id FROM tables WHERE restaurant_id = ? AND table_number = ?",
            [$restaurantId, $tableNumber]
        );

        if ($existing) {
            $this->setFlash('error', 'Ya existe una mesa con este número en el restaurante.');
            return $this->back();
        }

        // Insertar mesa
        $db->query(
            "INSERT INTO tables (restaurant_id, table_number, capacity, area, floor, is_available, can_be_joined) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$restaurantId, $tableNumber, $capacity, $area, $floor, $isAvailable, $canBeJoined]
        );

        // Registrar en audit log
        $this->auditLog->log(
            $_SESSION['user_id'],
            'added_table',
            'tables',
            $db->getConnection()->lastInsertId(),
            "Mesa #{$tableNumber} agregada al restaurante #{$restaurantId}"
        );

        $this->setFlash('success', 'Mesa agregada exitosamente.');
        return redirect_to('/admin/restaurants/' . $restaurantId . '/tables');
    }

    public function updateTable($restaurantId)
    {
        $tableId = (int)$this->input('table_id');
        $tableNumber = $this->sanitize($this->input('table_number'));
        $capacity = (int)$this->input('capacity');
        $area = $this->sanitize($this->input('area'));
        $floor = (int)$this->input('floor') ?: 1;
        $isAvailable = $this->input('is_available') ? 1 : 0;
        $canBeJoined = $this->input('can_be_joined') ? 1 : 0;

        // Validaciones
        if (empty($tableId) || empty($tableNumber) || $capacity < 1) {
            $this->setFlash('error', 'Por favor completa todos los campos requeridos.');
            return $this->back();
        }

        $db = Database::getInstance();

        // Verificar que la mesa pertenece al restaurante
        $table = $db->fetchOne(
            "SELECT id FROM tables WHERE id = ? AND restaurant_id = ?",
            [$tableId, $restaurantId]
        );

        if (!$table) {
            $this->setFlash('error', 'Mesa no encontrada.');
            return $this->back();
        }

        // Verificar que no exista otra mesa con el mismo número en este restaurante
        $existing = $db->fetchOne(
            "SELECT id FROM tables WHERE restaurant_id = ? AND table_number = ? AND id != ?",
            [$restaurantId, $tableNumber, $tableId]
        );

        if ($existing) {
            $this->setFlash('error', 'Ya existe otra mesa con este número en el restaurante.');
            return $this->back();
        }

        // Actualizar mesa
        $db->query(
            "UPDATE tables SET table_number = ?, capacity = ?, area = ?, floor = ?, 
             is_available = ?, can_be_joined = ? WHERE id = ?",
            [$tableNumber, $capacity, $area, $floor, $isAvailable, $canBeJoined, $tableId]
        );

        // Registrar en audit log
        $this->auditLog->log(
            $_SESSION['user_id'],
            'updated_table',
            'tables',
            $tableId,
            "Mesa #{$tableNumber} actualizada"
        );

        $this->setFlash('success', 'Mesa actualizada exitosamente.');
        return redirect_to('/admin/restaurants/' . $restaurantId . '/tables');
    }

    public function deleteTable($restaurantId)
    {
        $tableId = (int)$this->input('table_id');

        if (empty($tableId)) {
            $this->setFlash('error', 'Mesa no especificada.');
            return $this->back();
        }

        $db = Database::getInstance();

        // Verificar que la mesa pertenece al restaurante
        $table = $db->fetchOne(
            "SELECT table_number FROM tables WHERE id = ? AND restaurant_id = ?",
            [$tableId, $restaurantId]
        );

        if (!$table) {
            $this->setFlash('error', 'Mesa no encontrada.');
            return $this->back();
        }

        // Verificar si hay reservas asociadas a esta mesa
        $hasReservations = $db->fetchOne(
            "SELECT COUNT(*) as count FROM reservation_tables WHERE table_id = ?",
            [$tableId]
        );

        if ($hasReservations && $hasReservations['count'] > 0) {
            $this->setFlash('error', 'No se puede eliminar la mesa porque tiene reservas asociadas. Marca la mesa como no disponible en su lugar.');
            return $this->back();
        }

        // Eliminar mesa
        $db->query("DELETE FROM tables WHERE id = ?", [$tableId]);

        // Registrar en audit log
        $this->auditLog->log(
            $_SESSION['user_id'],
            'deleted_table',
            'tables',
            $tableId,
            "Mesa #{$table['table_number']} eliminada del restaurante #{$restaurantId}"
        );

        $this->setFlash('success', 'Mesa eliminada exitosamente.');
        return redirect_to('/admin/restaurants/' . $restaurantId . '/tables');
    }

    public function saveElement($restaurantId)
    {
        header('Content-Type: application/json');

        $row = (int)$this->input('row');
        $col = (int)$this->input('col');
        $area = $this->sanitize($this->input('area')) ?: 'Interior';
        $elementType = $this->sanitize($this->input('element_type')) ?: 'table';
        $tableNumber = $this->sanitize($this->input('table_number'));
        $capacity = (int)$this->input('capacity') ?: 4;
        $elementId = $this->input('element_id') ? (int)$this->input('element_id') : null;

        // Validar tipos permitidos
        $validTypes = ['table', 'stairs', 'bathroom', 'bar', 'door', 'wall', 'empty'];
        if (!in_array($elementType, $validTypes)) {
            echo json_encode(['success' => false, 'message' => 'Tipo de elemento no válido.']);
            exit;
        }

        // Validar posición
        if ($row < 1 || $row > 10 || $col < 1 || $col > 12) {
            echo json_encode(['success' => false, 'message' => 'Posición fuera de rango.']);
            exit;
        }

        $db = Database::getInstance();

        // Si es edición, verificar que el elemento pertenece al restaurante
        if ($elementId) {
            $existing = $db->fetchOne(
                "SELECT id FROM tables WHERE id = ? AND restaurant_id = ?",
                [$elementId, $restaurantId]
            );

            if (!$existing) {
                echo json_encode(['success' => false, 'message' => 'Elemento no encontrado.']);
                exit;
            }

            // Actualizar elemento existente
            $db->query(
                "UPDATE tables SET element_type = ?, table_number = ?, capacity = ?, 
                 position_x = ?, position_y = ?, area = ? WHERE id = ? AND restaurant_id = ?",
                [$elementType, $tableNumber, $capacity, $col, $row, $area, $elementId, $restaurantId]
            );

            $this->auditLog->log(
                $_SESSION['user_id'],
                'updated_element',
                'tables',
                $elementId,
                "Elemento actualizado: tipo={$elementType}, posición=({$col},{$row})"
            );

            echo json_encode(['success' => true, 'message' => 'Elemento actualizado correctamente.']);
        } else {
            // Verificar si ya hay un elemento en esa posición
            $conflict = $db->fetchOne(
                "SELECT id FROM tables WHERE restaurant_id = ? AND position_x = ? AND position_y = ? AND area = ?",
                [$restaurantId, $col, $row, $area]
            );

            if ($conflict) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un elemento en esa posición.']);
                exit;
            }

            // Crear nuevo elemento
            $db->query(
                "INSERT INTO tables (restaurant_id, element_type, table_number, capacity, area, floor, 
                 position_x, position_y, is_available, can_be_joined) 
                 VALUES (?, ?, ?, ?, ?, 1, ?, ?, 1, 0)",
                [$restaurantId, $elementType, $tableNumber, $capacity, $area, $col, $row]
            );

            $newId = $db->getConnection()->lastInsertId();

            $this->auditLog->log(
                $_SESSION['user_id'],
                'added_element',
                'tables',
                $newId,
                "Elemento creado: tipo={$elementType}, posición=({$col},{$row})"
            );

            echo json_encode(['success' => true, 'message' => 'Elemento agregado correctamente.']);
        }
        exit;
    }

    // ==================== MENU MANAGEMENT ====================

    public function menusAdmin()
    {
        $db = Database::getInstance();
        
        // Get all menu categories with restaurant info
        $categories = $db->fetchAll("
            SELECT mc.*, r.name as restaurant_name, r.id as restaurant_id,
                   (SELECT COUNT(*) FROM menu_items WHERE category_id = mc.id) as items_count
            FROM menu_categories mc
            LEFT JOIN restaurants r ON mc.restaurant_id = r.id
            ORDER BY r.name, mc.name
        ");

        $this->view('admin.menus', [
            'title' => 'Gestión de Menús - Admin',
            'categories' => $categories
        ]);
    }

    public function restaurantMenus($restaurantId)
    {
        $db = Database::getInstance();
        
        $restaurant = $db->fetchOne("SELECT * FROM restaurants WHERE id = ?", [$restaurantId]);
        
        if (!$restaurant) {
            $this->setFlash('error', 'Restaurante no encontrado');
            return $this->redirect('/admin/restaurants');
        }

        $categories = $db->fetchAll("
            SELECT mc.*, 
                   (SELECT COUNT(*) FROM menu_items WHERE category_id = mc.id) as items_count
            FROM menu_categories mc
            WHERE mc.restaurant_id = ?
            ORDER BY mc.name
        ", [$restaurantId]);

        $this->view('admin.restaurant_menus', [
            'title' => 'Menús de ' . $restaurant['name'],
            'restaurant' => $restaurant,
            'categories' => $categories
        ]);
    }

    public function editMenu($menuId)
    {
        $db = Database::getInstance();
        
        $category = $db->fetchOne("
            SELECT mc.*, r.name as restaurant_name 
            FROM menu_categories mc
            LEFT JOIN restaurants r ON mc.restaurant_id = r.id
            WHERE mc.id = ?
        ", [$menuId]);
        
        if (!$category) {
            $this->setFlash('error', 'Categoría no encontrada');
            return $this->redirect('/admin/menus');
        }

        $items = $db->fetchAll("
            SELECT * FROM menu_items 
            WHERE category_id = ? 
            ORDER BY id
        ", [$menuId]);

        $this->view('admin.edit_menu', [
            'title' => 'Editar Menú - ' . $category['name'],
            'category' => $category,
            'items' => $items
        ]);
    }

    public function updateMenu($menuId)
    {
        $db = Database::getInstance();
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'El nombre es obligatorio');
            return $this->redirect("/admin/menus/{$menuId}/edit");
        }

        $db->query("
            UPDATE menu_categories 
            SET name = ?, description = ?, is_active = ?
            WHERE id = ?
        ", [$name, $description, $isActive, $menuId]);

        $this->auditLog->log(
            $_SESSION['user_id'],
            'updated_menu',
            'menu_categories',
            $menuId,
            "Menú actualizado: {$name}"
        );

        $this->setFlash('success', 'Menú actualizado correctamente');
        return $this->redirect("/admin/menus/{$menuId}/edit");
    }

    public function deleteMenu($menuId)
    {
        $db = Database::getInstance();
        
        // Check if category has items
        $itemCount = $db->fetchOne("SELECT COUNT(*) as count FROM menu_items WHERE category_id = ?", [$menuId]);
        
        if ($itemCount['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar. La categoría tiene productos asociados.']);
            exit;
        }

        $category = $db->fetchOne("SELECT name FROM menu_categories WHERE id = ?", [$menuId]);
        
        $db->query("DELETE FROM menu_categories WHERE id = ?", [$menuId]);

        $this->auditLog->log(
            $_SESSION['user_id'],
            'deleted_menu',
            'menu_categories',
            $menuId,
            "Categoría eliminada: {$category['name']}"
        );

        echo json_encode(['success' => true, 'message' => 'Categoría eliminada correctamente']);
        exit;
    }

    public function toggleMenuActive()
    {
        $menuId = $_POST['menu_id'] ?? 0;
        
        if (!$menuId) {
            echo json_encode(['success' => false, 'message' => 'ID no válido']);
            exit;
        }

        $db = Database::getInstance();
        $current = $db->fetchOne("SELECT is_active, name FROM menu_categories WHERE id = ?", [$menuId]);
        
        if (!$current) {
            echo json_encode(['success' => false, 'message' => 'Menú no encontrado']);
            exit;
        }

        $newStatus = $current['is_active'] ? 0 : 1;
        $db->query("UPDATE menu_categories SET is_active = ? WHERE id = ?", [$newStatus, $menuId]);

        $this->auditLog->log(
            $_SESSION['user_id'],
            'toggle_menu_status',
            'menu_categories',
            $menuId,
            "Estado cambiado a " . ($newStatus ? 'activo' : 'inactivo') . ": {$current['name']}"
        );

        echo json_encode([
            'success' => true, 
            'is_active' => $newStatus,
            'message' => 'Estado actualizado correctamente'
        ]);
        exit;
    }

    // ==================== MENU ITEMS MANAGEMENT ====================

    public function editMenuItem($itemId)
    {
        $db = Database::getInstance();
        
        $item = $db->fetchOne("
            SELECT mi.*, mc.id as category_id, mc.name as category_name
            FROM menu_items mi
            LEFT JOIN menu_categories mc ON mi.category_id = mc.id
            WHERE mi.id = ?
        ", [$itemId]);
        
        if (!$item) {
            $this->setFlash('error', 'Producto no encontrado');
            return $this->redirect('/admin/menus');
        }

        $this->view('admin.edit_menu_item', [
            'title' => 'Editar Producto - ' . $item['name'],
            'item' => $item
        ]);
    }

    public function updateMenuItem($itemId)
    {
        $db = Database::getInstance();
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'El nombre es obligatorio');
            return $this->redirect("/admin/menu-items/{$itemId}/edit");
        }

        if ($price < 0) {
            $this->setFlash('error', 'El precio no puede ser negativo');
            return $this->redirect("/admin/menu-items/{$itemId}/edit");
        }

        $item = $db->fetchOne("SELECT * FROM menu_items WHERE id = ?", [$itemId]);
        
        if (!$item) {
            $this->setFlash('error', 'Producto no encontrado');
            return $this->redirect('/admin/menus');
        }

        $db->query("
            UPDATE menu_items 
            SET name = ?, description = ?, price = ?, is_available = ?
            WHERE id = ?
        ", [$name, $description, $price, $isAvailable, $itemId]);

        $this->auditLog->log(
            $_SESSION['user_id'],
            'updated_menu_item',
            'menu_items',
            $itemId,
            "Producto actualizado: {$name}"
        );

        $this->setFlash('success', 'Producto actualizado correctamente');
        return $this->redirect("/admin/menus/{$item['category_id']}/edit");
    }

    public function deleteMenuItem($itemId)
    {
        $db = Database::getInstance();
        
        $item = $db->fetchOne("SELECT * FROM menu_items WHERE id = ?", [$itemId]);
        
        if (!$item) {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit;
        }

        $categoryId = $item['category_id'];
        $db->query("DELETE FROM menu_items WHERE id = ?", [$itemId]);

        $this->auditLog->log(
            $_SESSION['user_id'],
            'deleted_menu_item',
            'menu_items',
            $itemId,
            "Producto eliminado: {$item['name']}"
        );

        echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
        exit;
    }

    /**
     * Orders management
     */
    public function ordersAdmin()
    {
        $db = Database::getInstance();
        
        $statusFilter = $_GET['status'] ?? '';
        $restaurantFilter = $_GET['restaurant'] ?? '';
        
        $query = "SELECT o.*, 
                  r.name as restaurant_name,
                  CONCAT(u.first_name, ' ', u.last_name) as customer_name,
                  (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
                  FROM orders o
                  LEFT JOIN restaurants r ON o.restaurant_id = r.id
                  LEFT JOIN users u ON o.user_id = u.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($statusFilter) {
            $query .= " AND o.status = ?";
            $params[] = $statusFilter;
        }
        
        if ($restaurantFilter) {
            $query .= " AND o.restaurant_id = ?";
            $params[] = $restaurantFilter;
        }
        
        $query .= " ORDER BY o.created_at DESC LIMIT 100";
        
        $orders = $db->fetchAll($query, $params);
        
        // Get all restaurants for filter
        $restaurants = $db->fetchAll("SELECT id, name FROM restaurants ORDER BY name");
        
        return $this->view('admin.orders', [
            'title' => 'Gestionar Pedidos',
            'orders' => $orders,
            'restaurants' => $restaurants,
            'statusFilter' => $statusFilter,
            'restaurantFilter' => $restaurantFilter
        ]);
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($orderId)
    {
        // Log para debug
        error_log("updateOrderStatus called with ID: " . $orderId);
        error_log("POST data: " . print_r($_POST, true));
        
        $db = Database::getInstance();
        $newStatus = $this->input('status');
        
        error_log("New status: " . $newStatus);
        
        $validStatuses = ['enviado', 'en_preparacion', 'listo', 'entregado', 'cancelado'];
        
        if (!in_array($newStatus, $validStatuses)) {
            $this->setFlash('error', 'Estado inválido: ' . $newStatus);
            return $this->redirect('/grg/admin/orders');
        }
        
        $order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        
        if (!$order) {
            $this->setFlash('error', 'Pedido no encontrado: #' . $orderId);
            return $this->redirect('/grg/admin/orders');
        }
        
        try {
            // Update order status using direct PDO
            $stmt = $db->getConnection()->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            
            // Verify update
            $updated = $db->fetchOne("SELECT status FROM orders WHERE id = ?", [$orderId]);
            
            if ($updated && $updated['status'] === $newStatus) {
                // Log the change
                $this->auditLog->log(
                    $_SESSION['user_id'],
                    'updated_order_status',
                    'orders',
                    $orderId,
                    "Estado actualizado de '{$order['status']}' a '{$newStatus}'"
                );
                
                $this->setFlash('success', "✓ Pedido #{$orderId} actualizado a: " . ucfirst(str_replace('_', ' ', $newStatus)));
            } else {
                $this->setFlash('error', 'El estado no se actualizó correctamente.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al actualizar: ' . $e->getMessage());
        }
        
        return $this->redirect('/grg/admin/orders');
    }

    /**
     * Complete order (simple GET method)
     */
    public function completeOrder($orderId)
    {
        try {
            $db = Database::getInstance();
            
            // Get order first
            $order = $db->fetchOne("SELECT id, status FROM orders WHERE id = ?", [$orderId]);
            
            if (!$order) {
                $this->setFlash('error', "Pedido #{$orderId} no encontrado.");
                return $this->redirect('/grg/admin/orders');
            }
            
            // Update using PDO with explicit commit
            $pdo = $db->getConnection();
            
            // Start transaction
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $success = $stmt->execute([':status' => 'entregado', ':id' => $orderId]);
            
            if ($success && $stmt->rowCount() > 0) {
                $pdo->commit();
                
                $this->auditLog->log(
                    $_SESSION['user_id'],
                    'completed_order',
                    'orders',
                    $orderId,
                    "Pedido marcado como completado (de: {$order['status']})"
                );
                
                $this->setFlash('success', "✓ Pedido #{$orderId} marcado como COMPLETADO");
            } else {
                $pdo->rollBack();
                $this->setFlash('error', 'No se pudo actualizar el pedido.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        return $this->redirect('/grg/admin/orders');
    }

    /**
     * Cancel order (simple GET method)
     */
    public function cancelOrder($orderId)
    {
        try {
            $db = Database::getInstance();
            
            // Get order first
            $order = $db->fetchOne("SELECT id, status FROM orders WHERE id = ?", [$orderId]);
            
            if (!$order) {
                $this->setFlash('error', "Pedido #{$orderId} no encontrado.");
                return $this->redirect('/grg/admin/orders');
            }
            
            // Update using PDO with explicit commit
            $pdo = $db->getConnection();
            
            // Start transaction
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $success = $stmt->execute([':status' => 'cancelado', ':id' => $orderId]);
            
            if ($success && $stmt->rowCount() > 0) {
                $pdo->commit();
                
                $this->auditLog->log(
                    $_SESSION['user_id'],
                    'cancelled_order',
                    'orders',
                    $orderId,
                    "Pedido cancelado (de: {$order['status']})"
                );
                
                $this->setFlash('success', "✓ Pedido #{$orderId} CANCELADO");
            } else {
                $pdo->rollBack();
                $this->setFlash('error', 'No se pudo cancelar el pedido.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error: ' . $e->getMessage());
        }
        
        return $this->redirect('/grg/admin/orders');
    }
}

