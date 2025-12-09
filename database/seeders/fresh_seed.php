<?php

$host = 'localhost:3307';
$db = 'grg_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Conectado a la base de datos\n\n";
    
    // PASO 1: LIMPIAR TODAS LAS TABLAS
    echo "=== LIMPIANDO BASE DE DATOS ===\n";
    
    $tables = [
        'order_items', 'orders', 'cart_items', 'menu_item_option_values', 'menu_item_options',
        'menu_items', 'menu_categories', 'reservation_tables', 'reservations',
        'restaurant_users', 'tables', 'restaurants', 'users', 'roles'
    ];
    
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($tables as $table) {
        try {
            $pdo->exec("TRUNCATE TABLE $table");
            echo "✓ $table\n";
        } catch (Exception $e) {
        }
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

    // Ajustar columnas requeridas para el plano si no existen
    try { $pdo->exec("ALTER TABLE tables ADD COLUMN element_type ENUM('mesa','escalera','bano','barra','puerta','pared') DEFAULT 'mesa' AFTER table_number"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE tables ADD COLUMN zone VARCHAR(100) NULL AFTER area"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE tables ADD COLUMN connected_zone VARCHAR(100) NULL AFTER zone"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE tables ADD COLUMN description TEXT NULL AFTER connected_zone"); } catch (Exception $e) {}
    
    echo "\n=== CREANDO ROLES ===\n";
    
    $roles = [
        [1, 'SUPERADMIN', 'Administrador del sistema'],
        [2, 'RESTAURANT_ADMIN', 'Administrador de restaurante'],
        [3, 'OWNER', 'Dueño de restaurante'],
        [4, 'CLIENTE', 'Cliente regular'],
    ];
    
    foreach ($roles as [$id, $name, $desc]) {
        $pdo->prepare("INSERT INTO roles (id, name, description) VALUES (?, ?, ?)")
            ->execute([$id, $name, $desc]);
        echo "✓ $name\n";
    }
    
    echo "\n=== CREANDO SUPERADMIN ===\n";
    
    $adminPassword = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("
        INSERT INTO users (role_id, first_name, last_name, email, password, phone, is_active, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
    ")->execute([1, 'Admin', 'Sistema', 'admin@grg.com', $adminPassword, '+54911234567']);
    
    $adminId = $pdo->lastInsertId();
    echo "✓ admin@grg.com / admin123\n";
    
    echo "\n=== RESTAURANTES CON MENÚS ===\n";
    
    $restaurants = [
        ['La Parrilla del Gaucho', 'Asadería tradicional', 'Calle Principal 123', 'Buenos Aires', '12:00', '23:30'],
        ['Trattoria Italia', 'Cocina italiana auténtica', 'Avenida Corrientes 456', 'Buenos Aires', '11:30', '00:00'],
        ['Sakura Sushi', 'Cocina japonesa y fusión', 'Paseo de la Reforma 789', 'CABA', '12:00', '23:00']
    ];
    
    $restaurantIds = [];
    foreach ($restaurants as [$name, $desc, $addr, $city, $open, $close]) {
        $pdo->prepare("
            INSERT INTO restaurants (owner_id, name, description, address, city, state, postal_code, 
                                    phone, email, opening_time, closing_time, is_active, requires_payment, 
                                    payment_status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 'Buenos Aires', '1000', '+5411', ?, ?, ?, 1, 0, 'paid', NOW(), NOW())
        ")->execute([$adminId, $name, $desc, $addr, $city, $name.'@grg.com', $open, $close]);
        
        $restId = $pdo->lastInsertId();
        $restaurantIds[] = $restId;
        $pdo->prepare("INSERT INTO restaurant_users (restaurant_id, user_id, role) VALUES (?, ?, ?)")
            ->execute([$restId, $adminId, 'OWNER']);
        
        echo "✓ $name (ID: $restId)\n";
    }
    
    echo "\n=== CREANDO MENUS ===\n";
    
    // Menú 1: La Parrilla
    createMenu($pdo, $restaurantIds[0], [
        ['Entradas', 'Para comenzar'],
        ['Carnes', 'Especialidad de la casa'],
        ['Bebidas', 'Bebidas frías y calientes']
    ], [
        [0, 'Tabla de Quesos', 'Selección premium', 450],
        [0, 'Provoleta', 'Con orégano', 320],
        [1, 'Bife de Chorizo', '400g, jugoso', 890],
        [1, 'Carne Vacuna Premium', '500g, corte especial', 1200],
        [1, 'Asado de Tira', 'Costillar a la parrilla', 750],
        [2, 'Vino Tinto Malbec', 'Catena Zapata', 320],
        [2, 'Cerveza Artesanal', 'Rubia 500ml', 120],
    ]);
    echo "✓ Menú: La Parrilla del Gaucho\n";
    
    // Menú 2: Trattoria
    createMenu($pdo, $restaurantIds[1], [
        ['Antipastos', 'Entrada italiana'],
        ['Pasta', 'Nuestras especialidades'],
        ['Postres', 'Dulces italianos']
    ], [
        [0, 'Burrata con Tomates', 'Queso fresco italiano', 380],
        [0, 'Tabla de Embutidos', 'Prosciutto, mortadela', 520],
        [1, 'Lasaña a la Boloñesa', 'Receta tradicional', 560],
        [1, 'Risotto con Hongos', 'Con trufas', 680],
        [1, 'Pasta Carbonara', 'Auténtica romana', 480],
        [2, 'Tiramisu', 'Clásico italiano', 180],
        [2, 'Panna Cotta', 'Salsa de frutos rojos', 160],
    ]);
    echo "✓ Menú: Trattoria Italia\n";
    
    // Menú 3: Sakura
    createMenu($pdo, $restaurantIds[2], [
        ['Entrada Asiática', 'Para comenzar'],
        ['Sushi y Rollos', 'Nuestro especial'],
        ['Bebidas', 'Bebidas variadas']
    ], [
        [0, 'Tabla Mixta', 'Gyoza, spring rolls', 420],
        [0, 'Sopa Miso', 'Tradicional japonesa', 180],
        [1, 'Sushi Mixto Premium', '18 piezas variadas', 780],
        [1, 'Rollo Dragon', 'Anguila, aguacate', 620],
        [1, 'Nigiri Salmón', '8 piezas', 540],
        [2, 'Sake Premium', 'Importado de Japón', 380],
        [2, 'Té Verde Matcha', 'Tradicional', 95],
    ]);
    echo "✓ Menú: Sakura Sushi\n";
    
    echo "\n=== USUARIOS CLIENTES ===\n";
    
    $usuarios = [
        ['Juan', 'García', 'juan@example.com', '+541198765432'],
        ['María', 'López', 'maria@example.com', '+541187654321'],
        ['Carlos', 'Martínez', 'carlos@example.com', '+541176543210']
    ];
    
    $userIds = [];
    $clientPassword = password_hash('cliente123', PASSWORD_BCRYPT);
    
    foreach ($usuarios as [$first, $last, $email, $phone]) {
        $pdo->prepare("
            INSERT INTO users (role_id, first_name, last_name, email, password, phone, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ")->execute([4, $first, $last, $email, $clientPassword, $phone]);
        
        $userId = $pdo->lastInsertId();
        $userIds[] = $userId;
        echo "✓ $first $last ($email)\n";
    }
    
    echo "\n=== MESAS POR RESTAURANTE ===\n";
    
    foreach ($restaurantIds as $idx => $restId) {
        $count = ($idx === 0) ? 8 : 6;
        for ($i = 1; $i <= $count; $i++) {
            $pdo->prepare("
                INSERT INTO tables (
                    restaurant_id, table_number, element_type, zone, connected_zone,
                    capacity, area, floor, position_x, position_y,
                    is_available, can_be_joined, description, created_at, updated_at
                ) VALUES (?, ?, 'mesa', 'General', NULL, ?, 'Comedor', 1, ?, ?, 1, 0, NULL, NOW(), NOW())
            ")->execute([
                $restId,
                $i,
                ($i % 3 == 0) ? 6 : (($i % 2 == 0) ? 4 : 2),
                ($i - 1) % 4 + 1,           // position_x as grid column (1..4)
                floor(($i - 1) / 4) + 1      // position_y as grid row (1..N)
            ]);
        }
        echo "✓ $count mesas en restaurante $restId\n";
    }
    
    echo "\n=== RESERVAS ===\n";
    
    $reservations = [
        [$userIds[0], $restaurantIds[0], '+1 day', '20:00', '22:00', 4, 'CONFIRMED', 'Juan García'],
        [$userIds[1], $restaurantIds[1], '+2 days', '19:30', '21:30', 2, 'CONFIRMED', 'María López'],
        [$userIds[2], $restaurantIds[2], '+3 days', '20:00', '21:30', 6, 'PENDING', 'Carlos Martínez']
    ];
    
    foreach ($reservations as [$userId, $restId, $dateOffset, $startTime, $endTime, $guests, $status, $name]) {
        $date = date('Y-m-d', strtotime($dateOffset));
        
        $pdo->prepare("
            INSERT INTO reservations (user_id, restaurant_id, reservation_date, start_time, end_time, 
                                     guest_count, status, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ")->execute([$userId, $restId, $date, $startTime, $endTime, $guests, $status]);
        
        $resId = $pdo->lastInsertId();
        
        $table = $pdo->query("SELECT id FROM tables WHERE restaurant_id = $restId LIMIT 1")->fetch();
        if ($table) {
            $pdo->prepare("INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)")
                ->execute([$resId, $table['id']]);
        }
        
        echo "✓ $name - $status ($date $startTime, $guests comensales)\n";
    }
    
    echo "\n✅ ¡COMPLETO!\n\n";
    echo "SUPERADMIN: admin@grg.com / admin123\n";
    echo "CLIENTES: juan@example.com, maria@example.com, carlos@example.com / cliente123\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

function createMenu($pdo, $restaurantId, $categories, $items) {
    $catMap = [];
    foreach ($categories as $cat) {
        $pdo->prepare("
            INSERT INTO menu_categories (restaurant_id, name, description, is_active, created_at, updated_at)
            VALUES (?, ?, ?, 1, NOW(), NOW())
        ")->execute([$restaurantId, $cat[0], $cat[1]]);
        $catMap[$cat[0]] = $pdo->lastInsertId();
    }
    
    foreach ($items as [$catIdx, $name, $desc, $price]) {
        $catId = $catMap[$categories[$catIdx][0]];
        $pdo->prepare("
            INSERT INTO menu_items (restaurant_id, category_id, name, description, price, is_active, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())
        ")->execute([$restaurantId, $catId, $name, $desc, $price]);
    }
}
?>
