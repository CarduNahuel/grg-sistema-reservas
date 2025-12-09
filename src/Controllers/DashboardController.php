<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Models\Restaurant as RestaurantModel;
use App\Models\Reservation;
use App\Models\Notification;

class DashboardController extends Controller
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function index()
    {
        $user = $this->authService->user();
        
        if (!$user) {
            return $this->redirect('/grg/auth/login');
        }

        // Get user role
        $userModel = new \App\Models\User();
        $role = $userModel->getRole($user['id']);

        // Redirect based on role
        if ($role['name'] === 'SUPERADMIN') {
            return $this->redirect('/grg/admin/dashboard');
        } elseif ($role['name'] === 'OWNER' || $role['name'] === 'RESTAURANT_ADMIN') {
            return $this->redirect('/grg/owner/dashboard');
        } else {
            return $this->redirect('/grg/client/dashboard');
        }
    }

    public function clientDashboard()
    {
        $user = $this->authService->user();
        $reservationModel = new Reservation();
        $notificationModel = new Notification();

        // Get upcoming reservations
        $upcomingReservations = $reservationModel->getUpcoming($user['id'], 5);
        
        // Get past reservations
        $pastReservations = $reservationModel->getPast($user['id'], 5);

        // Get notifications
        $notifications = $notificationModel->getByUser($user['id']);
        $unreadCount = $notificationModel->getUnreadCount($user['id']);

        $this->view('layouts.client', [
            'title' => 'Mi Panel - GRG',
            'user' => $user,
            'upcomingReservations' => $upcomingReservations,
            'pastReservations' => $pastReservations,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    public function ownerDashboard()
    {
        $user = $this->authService->user();
        $userModel = new \App\Models\User();
        $restaurantModel = new RestaurantModel();

        // Get user's restaurants
        $restaurants = $userModel->getRestaurants($user['id']);

        // Get stats for first restaurant (or show selection if multiple)
        $stats = [];
        $pendingReservations = [];
        $todayReservations = [];

        if (!empty($restaurants)) {
            $firstRestaurant = $restaurants[0];
            $stats = $restaurantModel->getStats($firstRestaurant['id']);
            $pendingReservations = $restaurantModel->getPendingReservations($firstRestaurant['id']);
            $todayReservations = $restaurantModel->getTodayReservations($firstRestaurant['id']);
        }

        $this->view('layouts.owner', [
            'title' => 'Panel de Gestión - GRG',
            'user' => $user,
            'restaurants' => $restaurants,
            'stats' => $stats,
            'pendingReservations' => $pendingReservations,
            'todayReservations' => $todayReservations
        ]);
    }

    public function adminDashboard()
    {
        $user = $this->authService->user();
        
        // Get system-wide stats
        $userModel = new \App\Models\User();
        $restaurantModel = new RestaurantModel();
        $reservationModel = new Reservation();

        $stats = [
            'total_users' => $userModel->count(),
            'total_restaurants' => $restaurantModel->count('is_active = TRUE'),
            'total_reservations' => $reservationModel->count(),
            'pending_reservations' => $reservationModel->count('status = ?', ['pending'])
        ];

        $this->view('layouts.admin', [
            'title' => 'Panel de Administración - GRG',
            'user' => $user,
            'stats' => $stats
        ]);
    }
}
