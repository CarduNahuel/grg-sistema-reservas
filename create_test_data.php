<?php
require 'bootstrap/app.php';

$config = require 'config/database.php';
$db = \App\Services\Database::getInstance($config);

echo "=== CREANDO DATOS DE PRUEBA ===\n\n";

// 1. Obtener el restaurante existente
$restaurant = $db->fetchOne("SELECT id FROM restaurants LIMIT 1");
if (!$restaurant) {
    echo "❌ No hay restaurante. Crea uno primero.\n";
    exit;
}
$restaurantId = $restaurant['id'];
echo "✓ Usando restaurante ID: {$restaurantId}\n\n";

// 2. Crear varios clientes
$clients = [];
$clientEmails = [
    'cliente1@example.com',
    'cliente2@example.com',
    'cliente3@example.com',
    'cliente4@example.com',
    'cliente5@example.com'
];

foreach ($clientEmails as $email) {
    // Verificar si existe
    $existing = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) {
        $clients[] = $existing['id'];
        echo "✓ Cliente existe: {$email} (ID: {$existing['id']})\n";
    } else {
        // Crear nuevo
        $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
        $firstName = explode('@', $email)[0];
        
        $newId = $db->insert(
            "INSERT INTO users (first_name, last_name, email, password, role_id, created_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$firstName, 'Apellido', $email, $hashedPassword, 4] // role_id=4 es cliente
        );
        
        $clients[] = $newId;
        echo "✓ Cliente creado: {$email} (ID: {$newId})\n";
    }
}

echo "\n=== CREANDO RESERVAS ===\n\n";

// 3. Obtener mesas disponibles
$tables = $db->fetchAll("SELECT id FROM tables WHERE element_type = 'mesa' LIMIT 5");
if (empty($tables)) {
    echo "❌ No hay mesas creadas.\n";
    exit;
}

// 4. Estados de reserva y variaciones
$statuses = ['pending', 'confirmed', 'completed'];
$zones = ['General', 'Ventana', 'Terraza', 'Patio'];

$reservationDate = date('Y-m-d', strtotime('+1 day'));
$startTimes = ['12:00', '13:30', '19:00', '20:30', '21:00'];

$reservationCount = 0;

foreach ($clients as $clientId) {
    foreach ($startTimes as $startTime) {
        // Seleccionar aleatoriamente
        $status = $statuses[array_rand($statuses)];
        $zone = $zones[array_rand($zones)];
        $guestCount = rand(2, 8);
        $tableId = $status === 'confirmed' ? $tables[array_rand($tables)]['id'] : null;
        
        $reservationId = $db->insert(
            "INSERT INTO reservations 
             (restaurant_id, user_id, reservation_date, start_time, end_time, guest_count, 
              status, preferred_zone, table_id, notes, created_at)
             VALUES (?, ?, ?, ?, DATE_ADD(?, INTERVAL 2 HOUR), ?, ?, ?, ?, ?, NOW())",
            [
                $restaurantId,
                $clientId,
                $reservationDate,
                $startTime,
                $reservationDate,
                $guestCount,
                $status,
                $zone,
                $tableId,
                "Reserva de prueba para {$guestCount} personas"
            ]
        );
        
        $reservationCount++;
        
        echo "✓ Reserva #{$reservationId}: Cliente ID:{$clientId}, {$guestCount} personas, {$startTime}, Status: {$status}";
        
        // Si está confirmada, asignar mesa en tabla junction
        if ($status === 'confirmed' && $tableId) {
            // Asignar mesa principal
            $db->insert(
                "INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)",
                [$reservationId, $tableId]
            );
            echo " → Mesa {$tableId} asignada";
        }
        echo "\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "✓ Clientes creados: " . count($clients) . "\n";
echo "✓ Reservas creadas: {$reservationCount}\n";
echo "\nPuedes acceder como:\n";
echo "Email: cliente1@example.com\n";
echo "Password: password123\n\n";
echo "¡Listo para testear! 🎉\n";
