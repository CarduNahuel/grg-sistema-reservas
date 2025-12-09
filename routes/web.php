<?php

// Home & Public Routes
use App\Middleware\GuestMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CSRFMiddleware;

$router->get('/', 'HomeController@index');
$router->get('/about', 'HomeController@about');

// Authentication Routes
$router->get('/auth/login', 'AuthController@showLogin', [GuestMiddleware::class]);
$router->post('/auth/login', 'AuthController@login');
$router->get('/auth/register', 'AuthController@showRegister', [GuestMiddleware::class]);
$router->post('/auth/register', 'AuthController@register', [CSRFMiddleware::class]);
$router->get('/auth/logout', 'AuthController@logout');

// Password Recovery Routes
$router->get('/auth/forgot-password', 'AuthController@showForgotPassword', [GuestMiddleware::class]);
$router->post('/auth/send-reset-link', 'AuthController@sendResetLink', [CSRFMiddleware::class]);
$router->get('/auth/reset-password/{token}', 'AuthController@showResetPassword', [GuestMiddleware::class]);
$router->post('/auth/reset-password', 'AuthController@resetPassword', [CSRFMiddleware::class]);

// Profile Routes
$router->get('/profile', 'AuthController@showProfile', [AuthMiddleware::class]);
$router->post('/profile', 'AuthController@updateProfile', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/profile/password', 'AuthController@changePassword', [AuthMiddleware::class, CSRFMiddleware::class]);

// Dashboard Routes
$router->get('/dashboard', 'DashboardController@index', [AuthMiddleware::class]);
$router->get('/client/dashboard', 'DashboardController@clientDashboard', [AuthMiddleware::class]);
$router->get('/owner/dashboard', 'DashboardController@ownerDashboard', [AuthMiddleware::class]);
$router->get('/admin/dashboard', 'DashboardController@adminDashboard', [AuthMiddleware::class]);

// Restaurant Routes (Public)
$router->get('/restaurants', 'RestaurantController@index');
$router->get('/restaurants/{id}', 'RestaurantController@show');
$router->post('/restaurants/availability', 'RestaurantController@checkAvailability');

// Restaurant Routes (Owner)
$router->get('/owner/restaurants', 'RestaurantController@listOwner', [AuthMiddleware::class]);
$router->get('/owner/restaurants/create', 'RestaurantController@create', [AuthMiddleware::class]);
$router->post('/owner/restaurants', 'RestaurantController@store', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->get('/owner/restaurants/{id}', 'RestaurantController@manage', [AuthMiddleware::class]);
$router->get('/owner/restaurants/{id}/edit', 'RestaurantController@edit', [AuthMiddleware::class]);
$router->post('/owner/restaurants/{id}', 'RestaurantController@update', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/restaurants/{id}/delete', 'RestaurantController@delete', [AuthMiddleware::class, CSRFMiddleware::class]);

// Reservation Routes
$router->get('/reservations', 'ReservationController@index', [AuthMiddleware::class]);
$router->get('/reservations/create/{restaurantId}', 'ReservationController@create', [AuthMiddleware::class]);
$router->post('/reservations', 'ReservationController@store', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->get('/reservations/{id}', 'ReservationController@show', [AuthMiddleware::class]);
$router->post('/reservations/{id}/modify', 'ReservationController@modify', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/reservations/{id}/confirm', 'ReservationController@confirm', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/reservations/{id}/reject', 'ReservationController@reject', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/reservations/{id}/cancel', 'ReservationController@cancel', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/reservations/{id}/reassign', 'ReservationController@reassign', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/reservations/{id}/checkin', 'ReservationController@checkIn', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/reservations/{id}/complete', 'ReservationController@complete', [AuthMiddleware::class, CSRFMiddleware::class]);

// Table Routes
$router->get('/owner/restaurants/{restaurantId}/tables', 'TableController@index', [AuthMiddleware::class]);
$router->get('/owner/restaurants/{restaurantId}/tables/create', 'TableController@create', [AuthMiddleware::class]);
$router->post('/owner/restaurants/{restaurantId}/tables', 'TableController@store', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->get('/owner/tables/{id}/edit', 'TableController@edit', [AuthMiddleware::class]);
$router->post('/owner/tables/{id}', 'TableController@update', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/tables/{id}/delete', 'TableController@delete', [AuthMiddleware::class, CSRFMiddleware::class]);

// Plano Routes (Restaurante configura su grilla)
$router->get('/owner/restaurants/{restaurantId}/plano', 'TableController@plano', [AuthMiddleware::class]);
$router->post('/owner/restaurants/{restaurantId}/plano/save', 'TableController@savePlano', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/restaurants/{restaurantId}/plano/move', 'TableController@movePlano', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/restaurants/{restaurantId}/plano/delete', 'TableController@deletePlano', [AuthMiddleware::class, CSRFMiddleware::class]);

// Plano público (cliente ve y elige mesa)
$router->get('/restaurants/{restaurantId}/plano', 'TableController@publicPlano');

// API Routes (sin middleware porque las páginas que las llaman ya están protegidas)
$router->get('/api/restaurants/{restaurantId}/plano', 'TableController@getPlanoData');
$router->get('/api/reservations/{reservationId}/tables', 'TableController@getReservationTablesByReservation');

// Notification Routes
$router->get('/notifications', 'NotificationController@index', [AuthMiddleware::class]);
$router->post('/notifications/{id}/read', 'NotificationController@markAsRead', [AuthMiddleware::class]);
$router->post('/notifications/read-all', 'NotificationController@markAllAsRead', [AuthMiddleware::class]);

// Admin Routes (User Management)
$router->get('/admin/users', 'AdminController@users', [AuthMiddleware::class]);
$router->post('/admin/users/toggle-active', 'AdminController@toggleActive', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/users/change-role', 'AdminController@changeRole', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/users/reset-password', 'AdminController@resetUserPassword', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->get('/admin/users/{id}/history', 'AdminController@viewUserHistory', [AuthMiddleware::class]);

// Admin Routes (Reservation Management)
$router->get('/admin/reservations', 'AdminController@reservations', [AuthMiddleware::class]);
$router->post('/admin/reservations/status', 'AdminController@updateReservationStatus', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/reservations/notify', 'AdminController@notifyReservation', [AuthMiddleware::class, CSRFMiddleware::class]);

// Admin Routes (Orders Management)
$router->get('/admin/orders', 'AdminController@ordersAdmin', [AuthMiddleware::class]);
$router->post('/admin/orders/{id}/status', 'AdminController@updateOrderStatus', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->get('/admin/orders/{id}/complete', 'AdminController@completeOrder', [AuthMiddleware::class]);
$router->get('/admin/orders/{id}/cancel', 'AdminController@cancelOrder', [AuthMiddleware::class]);

// API Routes
$router->post('/api/orders/update-status', 'APIController@updateOrderStatus');

// Admin Routes (Restaurant Management)
$router->get('/admin/restaurants', 'AdminController@restaurantsAdmin', [AuthMiddleware::class]);
$router->get('/admin/restaurants/{id}/edit', 'AdminController@editRestaurant', [AuthMiddleware::class]);
$router->post('/admin/restaurants/{id}/update', 'AdminController@updateRestaurant', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/restaurants/toggle-active', 'AdminController@toggleRestaurantActive', [AuthMiddleware::class, CSRFMiddleware::class]);

// Admin Routes (Menu Management)
$router->get('/admin/menus', 'AdminController@menusAdmin', [AuthMiddleware::class]);
$router->get('/admin/restaurants/{id}/menus', 'AdminController@restaurantMenus', [AuthMiddleware::class]);
$router->get('/admin/menus/{id}/edit', 'AdminController@editMenu', [AuthMiddleware::class]);
$router->post('/admin/menus/{id}/update', 'AdminController@updateMenu', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/menus/{id}/delete', 'AdminController@deleteMenu', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/menus/toggle-active', 'AdminController@toggleMenuActive', [AuthMiddleware::class, CSRFMiddleware::class]);

// Admin Routes (Table Management)
$router->get('/admin/restaurants/{id}/tables', 'AdminController@restaurantTables', [AuthMiddleware::class]);
$router->post('/admin/restaurants/{id}/tables/add', 'AdminController@addTable', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/restaurants/{id}/tables/update', 'AdminController@updateTable', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/restaurants/{id}/tables/delete', 'AdminController@deleteTable', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/restaurants/{id}/tables/element', 'AdminController@saveElement', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/restaurants/{id}/tables/layout', 'AdminController@saveTableLayout', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/restaurants/{id}/tables/reset', 'AdminController@resetTables', [AuthMiddleware::class, CSRFMiddleware::class]);

// Payment Routes
$router->get('/payments/restaurant/{id}', 'PaymentController@showPayment', [AuthMiddleware::class]);
$router->post('/payments/process', 'PaymentController@process', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->get('/payments/success', 'PaymentController@success', [AuthMiddleware::class]);
$router->get('/payments/cancel', 'PaymentController@cancel', [AuthMiddleware::class]);

// Menu Routes (Public)
$router->get('/restaurants/{id}/menu', 'MenuController@showPublicMenu');
$router->get('/menu/item/{id}', 'MenuController@showItem');

// Menu Routes (Owner)
$router->get('/owner/restaurants/{id}/menu', 'MenuController@manageMenu', [AuthMiddleware::class]);
$router->post('/owner/menu/category/store', 'MenuController@storeCategory', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/menu/item/store', 'MenuController@storeItem', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/menu/category/toggle', 'MenuController@toggleCategory', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/menu/item/toggle', 'MenuController@toggleItem', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/menu/category/delete', 'MenuController@deleteCategory', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/owner/menu/item/delete', 'MenuController@deleteItem', [AuthMiddleware::class, CSRFMiddleware::class]);

// Admin Routes (Menu Items Management)
$router->get('/admin/menu-items/{id}/edit', 'AdminController@editMenuItem', [AuthMiddleware::class]);
$router->post('/admin/menu-items/{id}/update', 'AdminController@updateMenuItem', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/admin/menu-items/{id}/delete', 'AdminController@deleteMenuItem', [AuthMiddleware::class, CSRFMiddleware::class]);

// Cart Routes
$router->get('/cart', 'CartController@index', [AuthMiddleware::class]);
$router->post('/cart/add', 'CartController@add', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/cart/update', 'CartController@updateQuantity', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/cart/remove', 'CartController@remove', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/cart/clear', 'CartController@clear', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->post('/cart/send', 'CartController@send', [AuthMiddleware::class, CSRFMiddleware::class]);
$router->get('/cart/count', 'CartController@getCount', [AuthMiddleware::class]);

// Order Routes
$router->get('/orders', 'OrderController@myOrders', [AuthMiddleware::class]);
$router->get('/orders/{id}', 'OrderController@show', [AuthMiddleware::class]);
$router->get('/owner/restaurants/{id}/orders', 'OrderController@restaurantOrders', [AuthMiddleware::class]);
