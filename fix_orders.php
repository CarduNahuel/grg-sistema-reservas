<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

echo "=== ARREGLANDO LA TABLA ORDERS ===\n\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

// Step 1: Delete any problematic triggers
echo "1. Eliminando triggers...\n";
try {
    $stmt = $pdo->prepare("SHOW TRIGGERS WHERE `Table` = 'orders'");
    $stmt->execute();
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($triggers as $trigger) {
        $triggerName = $trigger['Trigger'];
        echo "   Eliminando trigger: $triggerName\n";
        $pdo->exec("DROP TRIGGER IF EXISTS `$triggerName`");
    }
    echo "   ✓ Triggers eliminados\n\n";
} catch (Exception $e) {
    echo "   Info: {$e->getMessage()}\n\n";
}

// Step 2: Check current status column
echo "2. Revisando estructura actual...\n";
$stmt = $pdo->prepare("DESCRIBE orders");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
$statusColumn = null;
foreach ($columns as $col) {
    if ($col['Field'] === 'status') {
        $statusColumn = $col;
        echo "   Tipo actual: {$col['Type']}\n";
    }
}

// Step 3: Change ENUM to VARCHAR
echo "\n3. Cambiando columna status a VARCHAR(50)...\n";
try {
    $sql = "ALTER TABLE orders MODIFY COLUMN status VARCHAR(50) DEFAULT 'enviado'";
    $pdo->exec($sql);
    echo "   ✓ Columna modificada\n\n";
} catch (Exception $e) {
    echo "   Error: {$e->getMessage()}\n";
    exit;
}

// Step 4: Fill empty/null values
echo "4. Llenando valores vacíos o NULL...\n";
try {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'enviado' WHERE status IS NULL OR status = ''");
    $stmt->execute();
    echo "   ✓ " . $stmt->rowCount() . " filas actualizadas\n\n";
} catch (Exception $e) {
    echo "   Error: {$e->getMessage()}\n";
}

// Step 5: Verify the change
echo "5. Verificando cambios:\n";
$stmt = $pdo->prepare("DESCRIBE orders");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if ($col['Field'] === 'status') {
        echo "   Type: {$col['Type']}\n";
        echo "   Default: {$col['Default']}\n";
    }
}

echo "\n6. Revisando órdenes:\n";
$stmt = $pdo->prepare("SELECT id, status FROM orders LIMIT 5");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($orders as $order) {
    echo "   Orden #{$order['id']}: '{$order['status']}'\n";
}

echo "\n✓ Arreglo completado\n";
?>
