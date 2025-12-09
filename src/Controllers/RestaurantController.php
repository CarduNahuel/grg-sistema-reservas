<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Models\Restaurant as RestaurantModel;
use App\Models\Table as TableModel;

class RestaurantController extends Controller
{
    private $authService;
    private $restaurantModel;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->restaurantModel = new RestaurantModel();
    }

    public function index()
    {
        $keyword = $this->input('search', '');
        $city = $this->input('city', null);

        if ($keyword || $city) {
            $restaurants = $this->restaurantModel->search($keyword, $city);
        } else {
            $restaurants = $this->restaurantModel->getActive();
        }

        $this->view('restaurants.index', [
            'title' => 'Restaurantes - GRG',
            'restaurants' => $restaurants,
            'keyword' => $keyword,
            'city' => $city
        ]);
    }

    public function show($id)
    {
        $restaurant = $this->restaurantModel->find($id);

        if (!$restaurant || !$restaurant['is_active']) {
            $this->setFlash('error', 'Restaurante no encontrado.');
            return $this->redirect('/restaurants');
        }

        // Get tables
        $tableModel = new TableModel();
        $tables = $tableModel->getByRestaurant($id);
        
        // Group tables by area
        $tablesByArea = [];
        foreach ($tables as $table) {
            $area = $table['area'] ?? 'General';
            if (!isset($tablesByArea[$area])) {
                $tablesByArea[$area] = [];
            }
            $tablesByArea[$area][] = $table;
        }

        $this->view('restaurants.show', [
            'title' => $restaurant['name'] . ' - GRG',
            'restaurant' => $restaurant,
            'tables' => $tables,
            'tablesByArea' => $tablesByArea
        ]);
    }

    public function checkAvailability()
    {
        $restaurantId = $this->input('restaurant_id');
        $date = $this->input('date');
        $startTime = $this->input('start_time');
        $endTime = $this->input('end_time');

        if (!$restaurantId || !$date || !$startTime || !$endTime) {
            return $this->json(['success' => false, 'message' => 'Datos incompletos.'], 400);
        }

        $availableTables = $this->restaurantModel->getAvailableTables(
            $restaurantId,
            $date,
            $date . ' ' . $startTime,
            $date . ' ' . $endTime
        );

        return $this->json([
            'success' => true,
            'tables' => $availableTables
        ]);
    }

    // OWNER METHODS

    public function create()
    {
        if (!$this->authService->isAdmin()) {
            $this->setFlash('error', 'No tienes permisos para crear restaurantes.');
            return $this->redirect('/dashboard');
        }

        $this->view('restaurants.create', [
            'title' => 'Crear Restaurante - GRG'
        ]);
    }

    public function store()
    {
        if (!$this->authService->isAdmin()) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $validator = new \App\Services\Validator();
        
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:100',
            'phone' => 'required|max:20',
            'opening_time' => 'required',
            'closing_time' => 'required'
        ])) {
            $this->setFlash('error', $validator->getFirstError());
            return $this->back();
        }

        $userId = $this->authService->userId();
        $userModel = new \App\Models\User();
        
        // Check if this is first restaurant
        $restaurantCount = $userModel->getRestaurantCount($userId);
        $requiresPayment = $restaurantCount > 0;

        try {
            $restaurantId = $this->restaurantModel->create($this->sanitize([
                'owner_id' => $userId,
                'name' => $this->input('name'),
                'description' => $this->input('description'),
                'address' => $this->input('address'),
                'city' => $this->input('city'),
                'state' => $this->input('state'),
                'postal_code' => $this->input('postal_code'),
                'phone' => $this->input('phone'),
                'email' => $this->input('email'),
                'opening_time' => $this->input('opening_time'),
                'closing_time' => $this->input('closing_time'),
                'requires_payment' => $requiresPayment,
                'payment_status' => $requiresPayment ? 'pending' : 'paid'
            ]));

            // Add owner to restaurant_users
            $this->restaurantModel->addUser($restaurantId, $userId, 'OWNER');

            if ($requiresPayment) {
                // Redirect to payment
                $this->setFlash('success', 'Restaurante creado. Por favor, completa el pago para activarlo.');
                return $this->redirect('/grg/payments/restaurant/' . $restaurantId);
            }

            $this->setFlash('success', 'Restaurante creado exitosamente.');
            return $this->redirect('/grg/owner/restaurants/' . $restaurantId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al crear restaurante: ' . $e->getMessage());
            return $this->back();
        }
    }

    public function edit($id)
    {
        if (!$this->authService->canManageRestaurant($id)) {
            $this->setFlash('error', 'No tienes permisos para editar este restaurante.');
            return $this->redirect('/dashboard');
        }

        $restaurant = $this->restaurantModel->find($id);

        $this->view('restaurants.edit', [
            'title' => 'Editar Restaurante - GRG',
            'restaurant' => $restaurant
        ]);
    }

    public function update($id)
    {
        if (!$this->authService->canManageRestaurant($id)) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $validator = new \App\Services\Validator();
        
        if (!$validator->validate($_POST, [
            'name' => 'required|max:255',
            'address' => 'required|max:255',
            'city' => 'required|max:100',
            'phone' => 'required|max:20',
            'opening_time' => 'required',
            'closing_time' => 'required'
        ])) {
            $this->setFlash('error', $validator->getFirstError());
            return $this->back();
        }

        try {
            $this->restaurantModel->update($id, $this->sanitize([
                'name' => $this->input('name'),
                'description' => $this->input('description'),
                'address' => $this->input('address'),
                'city' => $this->input('city'),
                'state' => $this->input('state'),
                'postal_code' => $this->input('postal_code'),
                'phone' => $this->input('phone'),
                'email' => $this->input('email'),
                'opening_time' => $this->input('opening_time'),
                'closing_time' => $this->input('closing_time')
            ]));

            $this->setFlash('success', 'Restaurante actualizado exitosamente.');
            return $this->redirect('/grg/owner/restaurants/' . $id);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al actualizar restaurante: ' . $e->getMessage());
            return $this->back();
        }
    }

    public function delete($id)
    {
        if (!$this->authService->canManageRestaurant($id)) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        try {
            $this->restaurantModel->delete($id);
            $this->setFlash('success', 'Restaurante eliminado exitosamente.');
            return $this->redirect('/grg/owner/dashboard');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Error al eliminar restaurante: ' . $e->getMessage());
            return $this->back();
        }
    }

    public function listOwner()
    {
        if (!$this->authService->isAdmin()) {
            $this->setFlash('error', 'No tienes permisos para acceder a esta sección.');
            return $this->redirect('/dashboard');
        }

        $userId = $this->authService->userId();
        
        // Get all restaurants owned or managed by this user
        $restaurants = $this->restaurantModel->getByOwner($userId);

        $this->view('owner.restaurants.index', [
            'title' => 'Mis Restaurantes - GRG',
            'restaurants' => $restaurants
        ]);
    }

    public function manage($id)
    {
        if (!$this->authService->canManageRestaurant($id)) {
            $this->setFlash('error', 'No tienes permisos para gestionar este restaurante.');
            return $this->redirect('/dashboard');
        }

        $restaurant = $this->restaurantModel->find($id);
        $stats = $this->restaurantModel->getStats($id);
        $pendingReservations = $this->restaurantModel->getPendingReservations($id);
        $todayReservations = $this->restaurantModel->getTodayReservations($id);
        
        $tableModel = new TableModel();
        $tables = $tableModel->getByRestaurant($id);

        $this->view('restaurants.manage', [
            'title' => 'Gestionar ' . $restaurant['name'] . ' - GRG',
            'restaurant' => $restaurant,
            'stats' => $stats,
            'pendingReservations' => $pendingReservations,
            'todayReservations' => $todayReservations,
            'tables' => $tables
        ]);
    }
}
