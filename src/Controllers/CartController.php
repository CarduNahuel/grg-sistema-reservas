<?php

namespace App\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Notification;
use App\Models\Reservation;
use App\Services\Validator;
use App\Services\Database;

class CartController extends Controller
{
    private $cartModel;
    private $cartItemModel;
    private $menuItemModel;
    private $orderModel;
    private $notificationModel;

    public function __construct()
    {
        $this->cartModel = new Cart();
        $this->cartItemModel = new CartItem();
        $this->menuItemModel = new MenuItem();
        $this->orderModel = new Order();
        $this->notificationModel = new Notification();
    }

    /**
     * View cart
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('error', 'Debes iniciar sesión para ver tu carrito.');
            return $this->redirect('/grg/auth/login');
        }

        $userId = $_SESSION['user_id'];
        $cart = $this->cartModel->getActiveCart($userId);

        if (!$cart) {
            return $this->view('cart.empty', [
                'title' => 'Carrito Vacío'
            ]);
        }

        $cartDetails = $this->cartModel->getCartWithDetails($cart['id']);
        $items = $this->cartModel->getCartItems($cart['id']);
        $total = $this->cartModel->calculateTotal($cart['id']);

        return $this->view('cart.index', [
            'title' => 'Mi Carrito',
            'cart' => $cartDetails,
            'items' => $items,
            'total' => $total
        ]);
    }

    /**
     * Add item to cart
     */
    public function add()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Debes iniciar sesión.'], 401);
        }

        $userId = $_SESSION['user_id'];
        $menuItemId = $this->input('menu_item_id');
        $quantity = max(1, (int)$this->input('quantity', 1));
        $note = $this->sanitize($this->input('note', ''));
        $options = $this->input('options', []); // Array of option_value_id => extra_price

        // Validate menu item exists
        $menuItem = $this->menuItemModel->getWithDetails($menuItemId);
        if (!$menuItem || !$menuItem['is_active']) {
            return $this->json(['success' => false, 'message' => 'Producto no disponible.'], 404);
        }

        $restaurantId = $menuItem['restaurant_id'];

        // Get or create cart for this restaurant
        $cart = $this->cartModel->getActiveCart($userId, $restaurantId);

        if (!$cart) {
            // Create new cart
            $cartId = $this->cartModel->create([
                'user_id' => $userId,
                'restaurant_id' => $restaurantId,
                'status' => 'open'
            ]);

            // Check if user has active reservation for this restaurant
            $reservationModel = new Reservation();
            $activeReservation = $reservationModel->getUserActiveReservation($userId, $restaurantId);
            if ($activeReservation) {
                $this->cartModel->linkReservation($cartId, $activeReservation['id']);
            }
        } else {
            $cartId = $cart['id'];

            // Validate not mixing restaurants
            if ($cart['restaurant_id'] != $restaurantId) {
                return $this->json([
                    'success' => false,
                    'message' => 'No puedes agregar productos de diferentes restaurantes. Vacía tu carrito primero.'
                ], 400);
            }
        }

        // Add item to cart
        $cartItemId = $this->cartItemModel->addToCart(
            $cartId,
            $menuItemId,
            $quantity,
            $note,
            (float)$menuItem['price'],
            $options
        );

        if (!$cartItemId) {
            return $this->json(['success' => false, 'message' => 'Error al agregar al carrito.'], 500);
        }

        $itemCount = $this->cartModel->getItemCount($cartId);
        $total = $this->cartModel->calculateTotal($cartId);

        return $this->json([
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cart_item_count' => $itemCount,
            'cart_total' => number_format($total, 2)
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('error', 'No autorizado.');
            return $this->redirect('/grg/login');
        }

        $cartItemId = $this->input('cart_item_id');
        $quantity = max(1, (int)$this->input('quantity', 1));

        $updated = $this->cartItemModel->updateQuantity($cartItemId, $quantity);

        if (!$updated) {
            $this->setFlash('error', 'Error al actualizar cantidad.');
        } else {
            $this->setFlash('success', 'Cantidad actualizada.');
        }

        return $this->redirect('/grg/cart');
    }

    /**
     * Remove item from cart
     */
    public function remove()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('error', 'No autorizado.');
            return $this->redirect('/grg/login');
        }

        $cartItemId = $this->input('cart_item_id');

        $removed = $this->cartItemModel->removeFromCart($cartItemId);

        if (!$removed) {
            $this->setFlash('error', 'Error al eliminar producto.');
        } else {
            $this->setFlash('success', 'Producto eliminado.');
        }

        return $this->redirect('/grg/cart');
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('error', 'No autorizado.');
            return $this->back();
        }

        $userId = $_SESSION['user_id'];
        $cart = $this->cartModel->getActiveCart($userId);

        if ($cart) {
            $this->cartModel->clearCart($cart['id']);
        }

        $this->setFlash('success', 'Carrito vaciado.');
        return $this->redirect('/grg/cart');
    }

    /**
     * Send cart as order
     */
    public function send()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->setFlash('error', 'Debes iniciar sesión.');
            return $this->redirect('/grg/auth/login');
        }

        $userId = $_SESSION['user_id'];
        $customerPhone = $this->sanitize($this->input('customer_phone', ''));
        $paymentMethod = $this->input('payment_method', 'EFECTIVO');

        // Get active cart
        $cart = $this->cartModel->getActiveCart($userId);

        if (!$cart) {
            $this->setFlash('error', 'No tienes un carrito activo.');
            return $this->redirect('/grg/cart');
        }

        $cartDetails = $this->cartModel->getCartWithDetails($cart['id']);

        if (empty($cartDetails['items'])) {
            $this->setFlash('error', 'Tu carrito está vacío.');
            return $this->redirect('/grg/cart');
        }

        // Update phone if provided
        if ($customerPhone) {
            $this->cartModel->update($cart['id'], ['customer_phone' => $customerPhone]);
        }

        // Create order from cart
        $orderId = $this->orderModel->createFromCart($cart['id'], $paymentMethod);

        if (!$orderId) {
            $this->setFlash('error', 'Error al procesar el pedido.');
            return $this->back();
        }

        // Send notifications and emails
        $this->sendOrderNotifications($orderId);

        $this->setFlash('success', '¡Pedido enviado exitosamente! El restaurante lo recibirá pronto.');
        return $this->redirect('/grg/orders/' . $orderId);
    }

    /**
     * Send order notifications
     */
    private function sendOrderNotifications($orderId)
    {
        $order = $this->orderModel->getOrderWithDetails($orderId);

        if (!$order) {
            return;
        }

        // Create notification for restaurant owner
        $db = Database::getInstance();
        $restaurant = $db->fetchOne("SELECT owner_id FROM restaurants WHERE id = ?", [$order['restaurant_id']]);

        if ($restaurant && $restaurant['owner_id']) {
            $this->notificationModel->create([
                'user_id' => $restaurant['owner_id'],
                'type' => 'new_order',
                'title' => '¡Nuevo pedido recibido!',
                'message' => "Pedido #{$orderId} - Total: $" . number_format($order['total'], 2)
            ]);
        }

        // Send email to restaurant
        $this->sendRestaurantEmail($order);

        // Send email to customer
        $this->sendCustomerEmail($order);
    }

    /**
     * Send email to restaurant
     */
    private function sendRestaurantEmail($order)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
            $mail->Port = $_ENV['MAIL_PORT'] ?? 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@grg.com', 'GRG - Sistema de Pedidos');
            $mail->addAddress($order['restaurant_email'], $order['restaurant_name']);

            $itemsList = '';
            foreach ($order['items'] as $item) {
                $itemsList .= "<li>{$item['quantity']}x {$item['product_name']} - $" . number_format($item['subtotal'], 2);
                if (!empty($item['options'])) {
                    $itemsList .= " <ul>";
                    foreach ($item['options'] as $opt) {
                        $itemsList .= "<li>{$opt['option_label']} (+$" . number_format($opt['extra_price'], 2) . ")</li>";
                    }
                    $itemsList .= "</ul>";
                }
                if ($item['note']) {
                    $itemsList .= "<br><em>Nota: {$item['note']}</em>";
                }
                $itemsList .= "</li>";
            }

            $mail->isHTML(true);
            $mail->Subject = "Nuevo Pedido #{$order['id']} - {$order['restaurant_name']}";
            $mail->Body = "
                <h2>¡Nuevo Pedido Recibido!</h2>
                <p><strong>Pedido:</strong> #{$order['id']}</p>
                <p><strong>Cliente:</strong> {$order['customer_name']}</p>
                <p><strong>Teléfono:</strong> {$order['customer_phone']}</p>
                <p><strong>Método de pago:</strong> {$order['payment_method']}</p>
                <hr>
                <h3>Productos:</h3>
                <ul>{$itemsList}</ul>
                <hr>
                <h3>Total: $" . number_format($order['total'], 2) . "</h3>
                <p><small>Fecha: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "</small></p>
            ";

            $mail->send();
        } catch (\Exception $e) {
            error_log("Error enviando email al restaurante: {$mail->ErrorInfo}");
        }
    }

    /**
     * Send email to customer
     */
    private function sendCustomerEmail($order)
    {
        if (!$order['customer_email']) {
            return;
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
            $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
            $mail->Port = $_ENV['MAIL_PORT'] ?? 587;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@grg.com', 'GRG');
            $mail->addAddress($order['customer_email'], $order['customer_name']);

            $mail->isHTML(true);
            $mail->Subject = "Confirmación de Pedido #{$order['id']}";
            $mail->Body = "
                <h2>¡Tu pedido fue enviado con éxito!</h2>
                <p>Hola {$order['customer_name']},</p>
                <p>Tu pedido en <strong>{$order['restaurant_name']}</strong> ha sido enviado y será procesado pronto.</p>
                <hr>
                <p><strong>Número de pedido:</strong> #{$order['id']}</p>
                <p><strong>Total:</strong> $" . number_format($order['total'], 2) . "</p>
                <p><strong>Método de pago:</strong> {$order['payment_method']}</p>
                <hr>
                <p>Gracias por usar GRG.</p>
            ";

            $mail->send();
        } catch (\Exception $e) {
            error_log("Error enviando email al cliente: {$mail->ErrorInfo}");
        }
    }

    /**
     * Get cart count for navbar
     */
    public function getCount()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['count' => 0]);
        }

        $cart = $this->cartModel->getActiveCart($_SESSION['user_id']);
        $count = $cart ? $this->cartModel->getItemCount($cart['id']) : 0;

        return $this->json(['count' => $count]);
    }
}
