<?php
require 'bootstrap/app.php';

$config = require 'config/database.php';
$db = \App\Services\Database::getInstance($config);

echo "=== LIMPIEZA COMPLETA DE BASE DE DATOS ===\n\n";

// 1. Truncar todas las tablas
echo "1. Truncando todas las tablas...\n";
$tables = ['reservation_tables', 'reservations', 'tables', 'restaurants', 'password_resets', 'audit_logs', 'users'];

foreach ($tables as $table) {
    try {
        $db->query("SET FOREIGN_KEY_CHECKS = 0");
        $db->query("TRUNCATE TABLE {$table}");
        $db->query("SET FOREIGN_KEY_CHECKS = 1");
        echo "   ✓ {$table}\n";
    } catch (Exception $e) {
        echo "   ✗ Error en {$table}: " . $e->getMessage() . "\n";
    }
}

echo "\n2. Creando usuario administrador...\n";
$adminId = $db->insert(
    "INSERT INTO users (first_name, last_name, email, password, role_id, created_at) 
     VALUES (?, ?, ?, ?, ?, NOW())",
    ['Admin', 'Sistema', 'admin@system.com', password_hash('admin123', PASSWORD_BCRYPT), 1]
);
echo "   ✓ Admin creado (ID: {$adminId}, Email: admin@system.com, Pass: admin123)\n";

echo "\n3. Creando dueño de restaurante...\n";
$ownerId = $db->insert(
    "INSERT INTO users (first_name, last_name, email, password, role_id, created_at) 
     VALUES (?, ?, ?, ?, ?, NOW())",
    ['Juan', 'Pérez', 'owner@restaurant.com', password_hash('owner123', PASSWORD_BCRYPT), 2]
);
echo "   ✓ Dueño creado (ID: {$ownerId}, Email: owner@restaurant.com, Pass: owner123)\n";

echo "\n4. Creando clientes...\n";
$clientes = [];
for ($i = 1; $i <= 5; $i++) {
    $clientId = $db->insert(
        "INSERT INTO users (first_name, last_name, email, password, role_id, created_at) 
         VALUES (?, ?, ?, ?, ?, NOW())",
        ["Cliente{$i}", "Apellido{$i}", "cliente{$i}@test.com", password_hash('cliente123', PASSWORD_BCRYPT), 4]
    );
    $clientes[] = $clientId;
    echo "   ✓ Cliente {$i} creado (ID: {$clientId}, Email: cliente{$i}@test.com, Pass: cliente123)\n";
}

echo "\n5. Creando restaurante...\n";
$restaurantId = $db->insert(
    "INSERT INTO restaurants (name, owner_id, description, address, city, phone, email, opening_time, closing_time, is_active, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
    [
        'El Buen Sabor',
        $ownerId,
        'Restaurante familiar con comida tradicional',
        'Av. Principal 123',
        'Buenos Aires',
        '+54 11 1234-5678',
        'contacto@elbuensabor.com',
        '12:00:00',
        '23:00:00',
        1
    ]
);
echo "   ✓ Restaurante creado (ID: {$restaurantId}, Nombre: El Buen Sabor)\n";

echo "\n6. Creando plano (mesas y elementos)...\n";

// Crear mesas en diferentes posiciones
$mesas = [
    ['P2C3', 'mesa', 3, 2, 4, 'General'],
    ['P2C6', 'mesa', 6, 2, 4, 'General'],
    ['P2C9', 'mesa', 9, 2, 6, 'Ventana'],
    ['P4C3', 'mesa', 3, 4, 4, 'General'],
    ['P4C6', 'mesa', 6, 4, 6, 'General'],
    ['P4C9', 'mesa', 9, 4, 2, 'Ventana'],
    ['P6C3', 'mesa', 3, 6, 8, 'VIP'],
    ['P6C6', 'mesa', 6, 6, 4, 'VIP'],
    ['P6C9', 'mesa', 9, 6, 6, 'Terraza'],
    ['P8C3', 'mesa', 3, 8, 4, 'General'],
    ['P8C6', 'mesa', 6, 8, 4, 'General'],
    ['P8C9', 'mesa', 9, 8, 2, 'Terraza']
];

$mesaIds = [];
foreach ($mesas as $m) {
    $mesaId = $db->insert(
        "INSERT INTO tables (restaurant_id, element_type, position_x, position_y, table_number, capacity, zone, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
        [$restaurantId, $m[1], $m[2], $m[3], $m[0], $m[4], $m[5]]
    );
    $mesaIds[] = $mesaId;
    echo "   ✓ Mesa {$m[0]} (Cap: {$m[4]}, Zona: {$m[5]}) - ID: {$mesaId}\n";
}

// Agregar algunos elementos decorativos
$elementos = [
    ['escalera', 1, 1, 'ESC1', 0, 'General'],
    ['bano', 12, 1, 'BAÑO', 0, 'General'],
    ['barra', 1, 10, 'BARRA', 0, 'General'],
    ['puerta', 6, 10, 'ENTRADA', 0, 'General']
];

foreach ($elementos as $e) {
    $db->insert(
        "INSERT INTO tables (restaurant_id, element_type, position_x, position_y, table_number, capacity, zone, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
        [$restaurantId, $e[0], $e[1], $e[2], $e[3], $e[4], $e[5]]
    );
    echo "   ✓ {$e[0]} en ({$e[1]},{$e[2]})\n";
}

echo "\n7. Creando reservas de prueba...\n";

// Fecha de mañana
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$horas = ['12:00:00', '13:30:00', '19:00:00', '20:30:00'];

$reservaCount = 0;
foreach ($clientes as $idx => $clientId) {
    $hora = $horas[$idx % count($horas)];
    $guests = rand(2, 6);
    
    // Crear algunas pending y otras confirmed
    $status = ($idx % 2 === 0) ? 'pending' : 'confirmed';
    $mesaAsignada = null;
    
    $reservaId = $db->insert(
        "INSERT INTO reservations (restaurant_id, user_id, reservation_date, start_time, end_time, guest_count, status, preferred_zone, notes, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
        [
            $restaurantId,
            $clientId,
            $tomorrow,
            $hora,
            date('H:i:s', strtotime($hora) + 7200), // +2 horas
            $guests,
            $status,
            'General',
            "Reserva de prueba para {$guests} personas"
        ]
    );
    
    echo "   ✓ Reserva #{$reservaId}: Cliente" . ($idx+1) . ", {$guests} personas, {$hora}, Status: {$status}\n";
    $reservaCount++;
}

echo "\n=== RESUMEN ===\n";
echo "✓ Base de datos limpia\n";
echo "✓ 1 Admin creado\n";
echo "✓ 1 Dueño creado\n";
echo "✓ 5 Clientes creados\n";
echo "✓ 1 Restaurante creado\n";
echo "✓ 12 Mesas creadas\n";
echo "✓ 4 Elementos decorativos\n";
echo "✓ {$reservaCount} Reservas pending creadas\n\n";

echo "ACCESOS:\n";
echo "Admin: admin@system.com / admin123\n";
echo "Owner: owner@restaurant.com / owner123\n";
echo "Cliente: cliente1@test.com / cliente123\n\n";

echo "¡Sistema completamente reiniciado! 🎉\n";
