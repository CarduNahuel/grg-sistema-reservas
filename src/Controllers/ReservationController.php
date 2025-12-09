<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\Validator;
use App\Models\Reservation as ReservationModel;
use App\Models\Restaurant as RestaurantModel;
use App\Models\Table as TableModel;
use App\Models\Notification;
use App\Models\MenuItem;

class ReservationController extends Controller
{
    private $authService;
    private $reservationModel;
    private $restaurantModel;
    private $tableModel;
    private $notificationModel;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->reservationModel = new ReservationModel();
        $this->restaurantModel = new RestaurantModel();
        $this->tableModel = new TableModel();
        $this->notificationModel = new Notification();
    }

    public function index()
    {
        $userId = $this->authService->userId();
        $reservations = $this->reservationModel->getByUser($userId);

        $this->view('reservations.index', [
            'title' => 'Mis Reservas - GRG',
            'reservations' => $reservations
        ]);
    }

    public function create($restaurantId)
    {
        $restaurant = $this->restaurantModel->find($restaurantId);

        if (!$restaurant || !$restaurant['is_active']) {
            $this->setFlash('error', 'Restaurante no encontrado.');
            return $this->redirect('/restaurants');
        }

        // Plano del restaurante para selección visual de mesa
        $tables = $this->tableModel->getByRestaurant($restaurantId);

        $this->view('reservations.create', [
            'title' => 'Crear Reserva - ' . $restaurant['name'],
            'restaurant' => $restaurant,
            'tables' => $tables
        ]);
    }

    public function store()
    {
        $validator = new Validator();

        if (!$validator->validate($_POST, [
            'restaurant_id' => 'required|integer',
            'reservation_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'guest_count' => 'required|integer'
        ])) {
            $this->setFlash('error', $validator->getFirstError());
            return $this->back();
        }

        $restaurantId = $this->input('restaurant_id');
        $tableId = $this->input('table_id'); // opcional: asigna restaurante
        $preferredTableId = $this->input('table_id') ?: null;
        $preferredZone = $this->sanitize($this->input('preferred_zone', '')) ?: null;
        $selectedTable = $tableId ? $this->tableModel->find($tableId) : null;
        $date = $this->input('reservation_date');
        $startTime = $date . ' ' . $this->input('start_time');
        $endTime = $date . ' ' . $this->input('end_time');
        $guestCount = $this->input('guest_count');
        $specialRequests = $this->sanitize($this->input('special_requests', ''));

        // Validate date is not in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $this->setFlash('error', 'No puedes hacer reservas en el pasado.');
            return $this->back();
        }

        // Check table availability only if a specific mesa was chosen
        if ($tableId) {
            $isAvailable = $this->tableModel->isAvailable($tableId, $date, $startTime, $endTime);
            if (!$isAvailable) {
                $this->setFlash('error', 'La mesa seleccionada no está disponible en ese horario.');
                return $this->back();
            }
        }

        try {
            // Create reservation
            $reservationId = $this->reservationModel->create([
                'restaurant_id' => $restaurantId,
                'table_id' => $tableId ?: null,
                'preferred_table_id' => $preferredTableId,
                'preferred_zone' => $preferredZone,
                'user_id' => $this->authService->userId(),
                'reservation_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'guest_count' => $guestCount,
                'status' => 'pending',
                'special_requests' => $specialRequests
            ]);

            // If a table was selected, add it to reservation_tables
            if ($tableId) {
                $db = \App\Services\Database::getInstance();
                $db->query("INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)", [$reservationId, $tableId]);
            }

            // Create notification for client
            $restaurant = $this->restaurantModel->find($restaurantId);
            $this->notificationModel->createReservationNotification(
                'reservation_created',
                $this->authService->userId(),
                $reservationId,
                'Reserva Creada',
                "Tu reserva en {$restaurant['name']} para el {$date} está pendiente de confirmación."
            );

            // Create notification for restaurant owner/admin
            $restaurantUsers = $this->restaurantModel->getUsers($restaurantId);
            // Mensaje de preferencia de mesa
            $preferenceMsg = '';
            if ($selectedTable) {
                $tableNum = $selectedTable['table_number'] ?? $selectedTable['id'];
                $tableZone = $selectedTable['zone'] ?? ($selectedTable['area'] ?? 'General');
                $preferenceMsg = " Preferencia: Mesa {$tableNum} (zona {$tableZone}).";
            } elseif ($preferredZone) {
                $preferenceMsg = " Preferencia de zona: {$preferredZone}.";
            }

            foreach ($restaurantUsers as $user) {
                if (in_array($user['restaurant_role'], ['OWNER', 'RESTAURANT_ADMIN'])) {
                    $this->notificationModel->createReservationNotification(
                        'reservation_created',
                        $user['id'],
                        $reservationId,
                        'Nueva Reserva',
                        "Nueva reserva para el {$date} con {$guestCount} personas." . $preferenceMsg
                    );
                }
            }

            $this->setFlash('success', 'Reserva creada exitosamente. Pendiente de confirmación.');
            return $this->redirect('/grg/reservations/' . $reservationId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al crear reserva: ' . $e->getMessage());
            return $this->back();
        }
    }

    public function show($id)
    {
        $userId = $this->authService->userId();
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            $this->setFlash('error', 'Reserva no encontrada.');
            return $this->redirect('/reservations');
        }

        // Check permissions
        $canView = ($reservation['user_id'] == $userId) || 
                   $this->authService->canManageRestaurant($reservation['restaurant_id']);

        if (!$canView) {
            $this->setFlash('error', 'No tienes permisos para ver esta reserva.');
            return $this->redirect('/reservations');
        }

        // Get related data
        $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
        $table = $reservation['table_id'] ? $this->tableModel->find($reservation['table_id']) : null;

        $this->view('reservations.show', [
            'title' => 'Detalles de Reserva - GRG',
            'reservation' => $reservation,
            'restaurant' => $restaurant,
            'table' => $table
        ]);
    }

    public function confirm($id)
    {
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            return $this->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
        }

        if (!$this->authService->canManageRestaurant($reservation['restaurant_id'])) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        try {
            $this->reservationModel->confirm($id, $this->authService->userId());

            // Notify client
            $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
            $this->notificationModel->createReservationNotification(
                'reservation_confirmed',
                $reservation['user_id'],
                $id,
                'Reserva Confirmada',
                "Tu reserva en {$restaurant['name']} ha sido confirmada."
            );

            return $this->json(['success' => true, 'message' => 'Reserva confirmada exitosamente.']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function reject($id)
    {
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            return $this->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
        }

        if (!$this->authService->canManageRestaurant($reservation['restaurant_id'])) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $reason = $this->sanitize($this->input('reason', ''));

        try {
            $this->reservationModel->reject($id, $this->authService->userId(), $reason);

            // Notify client
            $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
            $this->notificationModel->createReservationNotification(
                'reservation_rejected',
                $reservation['user_id'],
                $id,
                'Reserva Rechazada',
                "Tu reserva en {$restaurant['name']} ha sido rechazada." . 
                ($reason ? " Motivo: {$reason}" : '')
            );

            return $this->json(['success' => true, 'message' => 'Reserva rechazada.']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cancel($id)
    {
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            $this->setFlash('error', 'Reserva no encontrada.');
            return $this->back();
        }

        // Only the user who made the reservation can cancel
        if ($reservation['user_id'] != $this->authService->userId()) {
            $this->setFlash('error', 'No puedes cancelar esta reserva.');
            return $this->back();
        }

        // Check if reservation is in the future
        if (strtotime($reservation['start_time']) < time()) {
            $this->setFlash('error', 'No puedes cancelar una reserva pasada.');
            return $this->back();
        }

        try {
            $this->reservationModel->cancel($id);

            // Notify restaurant
            $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
            $restaurantUsers = $this->restaurantModel->getUsers($reservation['restaurant_id']);
            
            foreach ($restaurantUsers as $user) {
                if (in_array($user['restaurant_role'], ['OWNER', 'RESTAURANT_ADMIN'])) {
                    $this->notificationModel->createReservationNotification(
                        'reservation_cancelled',
                        $user['id'],
                        $id,
                        'Reserva Cancelada',
                        "Una reserva para el {$reservation['reservation_date']} ha sido cancelada por el cliente."
                    );
                }
            }

            $this->setFlash('success', 'Reserva cancelada exitosamente.');
            return $this->redirect('/reservations');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al cancelar reserva: ' . $e->getMessage());
            return $this->back();
        }
    }

    public function reassign($id)
    {
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            return $this->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
        }

        if (!$this->authService->canManageRestaurant($reservation['restaurant_id'])) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $newTableId = $this->input('table_id');

        if (!$newTableId) {
            return $this->json(['success' => false, 'message' => 'Mesa no especificada.'], 400);
        }

        try {
            $this->reservationModel->reassign($id, $newTableId);

            // Notify client
            $table = $this->tableModel->find($newTableId);
            $this->notificationModel->createReservationNotification(
                'reservation_confirmed',
                $reservation['user_id'],
                $id,
                'Reserva Actualizada',
                "Tu reserva ha sido reasignada a la mesa {$table['table_number']} ({$table['area']})."
            );

            return $this->json(['success' => true, 'message' => 'Reserva reasignada exitosamente.']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function checkIn($id)
    {
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            return $this->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
        }

        if (!$this->authService->canManageRestaurant($reservation['restaurant_id'])) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        try {
            $this->reservationModel->checkIn($id);
            return $this->json(['success' => true, 'message' => 'Check-in registrado exitosamente.']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function modify($id)
    {
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            return $this->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
        }

        // Solo el propietario puede modificar su reserva
        if ($reservation['user_id'] != $this->authService->userId()) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        // Solo se puede modificar si está pendiente o confirmada
        if (!in_array($reservation['status'], ['pending', 'confirmed'])) {
            return $this->json(['success' => false, 'message' => 'No se puede modificar una reserva en este estado.'], 400);
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $newDate = $input['reservation_date'] ?? null;
            $newStartTime = $input['start_time'] ?? null;
            $newGuestCount = $input['guest_count'] ?? null;

            if (!$newDate || !$newStartTime || !$newGuestCount) {
                return $this->json(['success' => false, 'message' => 'Parámetros inválidos.'], 400);
            }

            // Validar que la fecha sea en el futuro
            if (strtotime($newDate . ' ' . $newStartTime) <= time()) {
                return $this->json(['success' => false, 'message' => 'La fecha y hora deben ser en el futuro.'], 400);
            }

            // Validar disponibilidad de mesas
            $restaurant = $this->restaurantModel->find($reservation['restaurant_id']);
            $endTime = date('H:i', strtotime($newStartTime) + (120 * 60)); // 2 horas de duración estándar

            // Obtener mesas disponibles para la nueva fecha/hora
            $availableTables = $this->tableModel->getAvailableTables(
                $reservation['restaurant_id'],
                $newDate,
                $newStartTime,
                $endTime,
                $newGuestCount
            );

            if (empty($availableTables)) {
                return $this->json([
                    'success' => false,
                    'message' => 'No hay mesas disponibles para la nueva fecha y hora solicitada.'
                ], 400);
            }

            // Actualizar reserva
            $this->reservationModel->update($id, [
                'reservation_date' => $newDate,
                'start_time' => $newStartTime,
                'end_time' => $endTime,
                'guest_count' => $newGuestCount,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Registrar en audit log
            $auditLog = new \App\Models\AuditLog();
            $auditLog->log(
                $this->authService->userId(),
                'modified_reservation',
                'reservations',
                $id,
                "Reserva modificada: Fecha anterior: {$reservation['reservation_date']} -> Nueva: $newDate"
            );

            return $this->json(['success' => true, 'message' => 'Reserva modificada correctamente.']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function complete($id)
    {
        $reservation = $this->reservationModel->find($id);

        if (!$reservation) {
            return $this->json(['success' => false, 'message' => 'Reserva no encontrada.'], 404);
        }

        if (!$this->authService->canManageRestaurant($reservation['restaurant_id'])) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        try {
            $this->reservationModel->complete($id);
            return $this->json(['success' => true, 'message' => 'Reserva completada exitosamente.']);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

