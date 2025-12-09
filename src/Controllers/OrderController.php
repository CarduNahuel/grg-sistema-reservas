<?php

namespace App\Controllers;

use App\Models\Order;
use App\Services\AuthService;

class OrderController extends Controller
{
    private $orderModel;
    private $authService;

    public function __construct()
    {
        $this->orderModel = new Order();
        $this->authService = new AuthService();
    }

    /**
     * Show order details
     */
    public function show($orderId)
    {
        $order = $this->orderModel->getOrderWithDetails($orderId);

        if (!$order) {
            $this->setFlash('error', 'Pedido no encontrado.');
            return $this->redirect('/grg/');
        }

        // Verify authorization
        if (!isset($_SESSION['user_id']) || 
            ($_SESSION['user_id'] != $order['user_id'] && !$this->authService->isOwner())) {
            $this->setFlash('error', 'No autorizado.');
            return $this->redirect('/grg/');
        }

        $items = $this->orderModel->getOrderItems($orderId);

        return $this->view('orders.show', [
            'title' => 'Pedido #' . $order['id'],
            'order' => $order,
            'items' => $items
        ]);
    }

    /**
     * List user orders
     */
    public function myOrders()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('error', 'Debes iniciar sesión.');
            return $this->redirect('/grg/auth/login');
        }

        $orders = $this->orderModel->getByUser($_SESSION['user_id']);

        return $this->view('orders.index', [
            'title' => 'Mis Pedidos',
            'orders' => $orders
        ]);
    }

    /**
     * List restaurant orders (for owner/admin)
     */
    public function restaurantOrders($restaurantId)
    {
        // TODO: Verify owner owns this restaurant
        $orders = $this->orderModel->getByRestaurant($restaurantId);

        return $this->view('orders.restaurant', [
            'title' => 'Pedidos del Restaurante',
            'orders' => $orders,
            'restaurant_id' => $restaurantId
        ]);
    }
}
