<?php
$dbHost = 'localhost:3307';
$dbUser = 'root';
$dbPass = '';
$dbName = 'grg_db';

try {
    $pdo = new PDO('mysql:host=' . $dbHost . ';dbname=' . $dbName, $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener usuarios
    $users = $pdo->query('SELECT id, first_name, last_name FROM users LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
    echo "📋 Usuarios disponibles:\n";
    foreach ($users as $user) {
        echo "  ID: {$user['id']} - {$user['first_name']} {$user['last_name']}\n";
    }
    
    // Obtener mesas
    $tables = $pdo->query('SELECT id, table_number, zone FROM tables ORDER BY zone, table_number')->fetchAll(PDO::FETCH_ASSOC);
    echo "\n📊 Mesas disponibles (" . count($tables) . "):\n";
    foreach ($tables as $table) {
        echo "  ID: {$table['id']} - Mesa {$table['table_number']} ({$table['zone']})\n";
    }
    
    echo "\n\n🔄 Creando 6 reservas de prueba...\n";
    
    // Crear 6 reservas con diferentes usuarios, fechas y estados
    $reservations = [
        [
            'user_id' => 2,
            'restaurant_id' => 1,
            'guest_count' => 4,
            'preferred_zone' => 'General',
            'start_time' => '2025-12-08 19:00:00',
            'status' => 'confirmed',
            'table_ids' => [1, 2]
        ],
        [
            'user_id' => 3,
            'restaurant_id' => 1,
            'guest_count' => 2,
            'preferred_zone' => 'Ventana',
            'start_time' => '2025-12-08 20:00:00',
            'status' => 'pending',
            'table_ids' => [7]
        ],
        [
            'user_id' => 4,
            'restaurant_id' => 1,
            'guest_count' => 6,
            'preferred_zone' => 'VIP',
            'start_time' => '2025-12-08 20:30:00',
            'status' => 'confirmed',
            'table_ids' => [11, 12]
        ],
        [
            'user_id' => 5,
            'restaurant_id' => 1,
            'guest_count' => 3,
            'preferred_zone' => 'Terraza',
            'start_time' => '2025-12-09 18:00:00',
            'status' => 'pending',
            'table_ids' => [15]
        ],
        [
            'user_id' => 6,
            'restaurant_id' => 1,
            'guest_count' => 5,
            'preferred_zone' => 'General',
            'start_time' => '2025-12-09 19:30:00',
            'status' => 'confirmed',
            'table_ids' => [3, 4]
        ],
        [
            'user_id' => 7,
            'restaurant_id' => 1,
            'guest_count' => 2,
            'preferred_zone' => 'VIP',
            'start_time' => '2025-12-09 21:00:00',
            'status' => 'completed',
            'table_ids' => [11]
        ]
    ];
    
    foreach ($reservations as $idx => $resData) {
        // Insertar reserva
        $stmt = $pdo->prepare('
            INSERT INTO reservations (user_id, restaurant_id, guest_count, preferred_zone, start_time, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ');
        $stmt->execute([
            $resData['user_id'],
            $resData['restaurant_id'],
            $resData['guest_count'],
            $resData['preferred_zone'],
            $resData['start_time'],
            $resData['status']
        ]);
        
        $reservationId = $pdo->lastInsertId();
        
        // Insertar mesas asignadas
        foreach ($resData['table_ids'] as $tableId) {
            $stmt = $pdo->prepare('
                INSERT INTO reservation_tables (reservation_id, table_id)
                VALUES (?, ?)
            ');
            $stmt->execute([$reservationId, $tableId]);
        }
        
        $user = $users[array_search($resData['user_id'], array_column($users, 'id'))];
        echo "  ✓ Reserva #{$reservationId} - {$user['first_name']} {$user['last_name']} - " . date('d/m/Y H:i', strtotime($resData['start_time'])) . " ({$resData['status']})\n";
    }
    
    // Mostrar estadísticas finales
    $totalRes = $pdo->query('SELECT COUNT(*) as cnt FROM reservations')->fetch(PDO::FETCH_ASSOC)['cnt'];
    $totalResTables = $pdo->query('SELECT COUNT(*) as cnt FROM reservation_tables')->fetch(PDO::FETCH_ASSOC)['cnt'];
    
    echo "\n\n✅ Reservas creadas exitosamente!\n";
    echo "📊 Estadísticas:\n";
    echo "  • Reservas totales: $totalRes\n";
    echo "  • Entradas en reservation_tables: $totalResTables\n";
    
} catch (Exception $e) {
    echo '❌ Error: ' . $e->getMessage();
}
?>
