<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=grg_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== LIMPIANDO BASE DE DATOS ===\n\n";

// Disable foreign keys temporarily
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

// List of tables to truncate
$tables = [
    'reservation_tables',
    'reservations',
    'notifications',
    'tables',
    'restaurant_users',
    'restaurants',
    'users'
];

foreach ($tables as $table) {
    try {
        $pdo->exec("TRUNCATE TABLE $table");
        echo "✓ Tabla $table truncada\n";
    } catch (Exception $e) {
        echo "✗ Error truncando $table: " . $e->getMessage() . "\n";
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

echo "\n=== RECREANDO DATOS INICIALES ===\n\n";

// Roles IDs
$ADMIN_ROLE = 1;
$OWNER_ROLE = 2;
$CLIENT_ROLE = 4;

// Create admin user
$pdo->exec("INSERT INTO users (role_id, email, password, first_name, last_name, is_active, created_at, updated_at) 
VALUES ({$ADMIN_ROLE}, 'admin@system.com', '" . password_hash('admin123', PASSWORD_BCRYPT) . "', 'Admin', 'System', 1, NOW(), NOW())");
$adminId = $pdo->lastInsertId();
echo "✓ Usuario admin creado (ID: {$adminId})\n";

// Create test user (cliente)
$pdo->exec("INSERT INTO users (role_id, email, password, first_name, last_name, is_active, created_at, updated_at) 
VALUES ({$CLIENT_ROLE}, 'cliente@example.com', '" . password_hash('pass123', PASSWORD_BCRYPT) . "', 'Pedro', 'Rodriguez', 1, NOW(), NOW())");
$clientId = $pdo->lastInsertId();
echo "✓ Usuario cliente creado (ID: {$clientId})\n";

// Create restaurant owner
$pdo->exec("INSERT INTO users (role_id, email, password, first_name, last_name, is_active, created_at, updated_at) 
VALUES ({$OWNER_ROLE}, 'owner@restaurant.com', '" . password_hash('pass123', PASSWORD_BCRYPT) . "', 'Juan', 'Restaurateur', 1, NOW(), NOW())");
$ownerId = $pdo->lastInsertId();
echo "✓ Usuario dueño creado (ID: {$ownerId})\n";

// Create restaurant
$pdo->exec("INSERT INTO restaurants (owner_id, name, description, address, phone, email, is_active, created_at, updated_at) 
VALUES ({$ownerId}, 'La Parrilla Argentina', 'Auténtico restaurant de carnes y parrilla', 'Calle Principal 123', '555-1234', 'info@parrilla.com', 1, NOW(), NOW())");
$restaurantId = $pdo->lastInsertId();
echo "✓ Restaurant creado (ID: {$restaurantId})\n";

// Create tables (mesas)
$tables_data = [
    ['position_x' => 4, 'position_y' => 4, 'element_type' => 'mesa', 'table_number' => 'P4C4', 'capacity' => 4, 'zone' => 'General'],
    ['position_x' => 5, 'position_y' => 4, 'element_type' => 'mesa', 'table_number' => 'P5C4', 'capacity' => 6, 'zone' => 'General'],
    ['position_x' => 6, 'position_y' => 4, 'element_type' => 'mesa', 'table_number' => 'P6C4', 'capacity' => 2, 'zone' => 'General'],
    ['position_x' => 7, 'position_y' => 5, 'element_type' => 'mesa', 'table_number' => 'P7C5', 'capacity' => 8, 'zone' => 'VIP'],
    ['position_x' => 4, 'position_y' => 2, 'element_type' => 'escalera', 'table_number' => 'ESC1', 'capacity' => 0, 'zone' => 'General', 'connected_zone' => 'Terraza'],
];

foreach ($tables_data as $table) {
    $connectedZone = isset($table['connected_zone']) ? "'{$table['connected_zone']}'" : "NULL";
    $pdo->exec("INSERT INTO tables (restaurant_id, position_x, position_y, element_type, table_number, capacity, zone, connected_zone) 
    VALUES ({$restaurantId}, {$table['position_x']}, {$table['position_y']}, '{$table['element_type']}', '{$table['table_number']}', {$table['capacity']}, '{$table['zone']}', {$connectedZone})");
}
echo "✓ Mesas creadas (5 mesas + 1 escalera)\n";

// Create test reservations
// Pending reservation
$pdo->exec("INSERT INTO reservations (restaurant_id, user_id, reservation_date, start_time, end_time, guest_count, status, preferred_zone, created_at, updated_at)
VALUES ({$restaurantId}, {$clientId}, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '19:00:00', '21:00:00', 3, 'pending', 'General', NOW(), NOW())");
$res1 = $pdo->lastInsertId();
echo "✓ Reserva pendiente #$res1 creada\n";

// Another pending reservation
$pdo->exec("INSERT INTO reservations (restaurant_id, user_id, reservation_date, start_time, end_time, guest_count, status, preferred_zone, created_at, updated_at)
VALUES ({$restaurantId}, {$clientId}, DATE_ADD(CURDATE(), INTERVAL 2 DAY), '20:00:00', '22:00:00', 6, 'pending', 'VIP', NOW(), NOW())");
$res2 = $pdo->lastInsertId();
echo "✓ Reserva pendiente #$res2 creada\n";

echo "\n=== BASE DE DATOS LISTA ===\n";
echo "Credenciales de prueba:\n";
echo "Admin: admin@system.com / admin123\n";
echo "Cliente: cliente@example.com / pass123\n";
echo "Dueño: owner@restaurant.com / pass123\n";

