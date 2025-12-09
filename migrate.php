<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

echo "=== MIGRANDO TABLA ORDERS ===\n\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

// First, check current status
echo "1. Estado actual de la columna status:\n";
$stmt = $pdo->prepare("DESCRIBE orders");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if ($col['Field'] === 'status') {
        echo "   Field: {$col['Field']}\n";
        echo "   Type: {$col['Type']}\n";
        echo "   Default: {$col['Default']}\n\n";
    }
}

// Execute the migration
echo "2. Ejecutando ALTER TABLE...\n";
try {
    $sql = "ALTER TABLE orders MODIFY COLUMN status ENUM('enviado', 'en_preparacion', 'listo', 'entregado', 'cancelado') DEFAULT 'enviado'";
    $pdo->exec($sql);
    echo "   ✓ Tabla actualizada exitosamente\n\n";
} catch (PDOException $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
    exit;
}

// Verify the change
echo "3. Verificando el cambio:\n";
$stmt = $pdo->prepare("DESCRIBE orders");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if ($col['Field'] === 'status') {
        echo "   Type: {$col['Type']}\n";
        echo "   Default: {$col['Default']}\n\n";
    }
}

// Check order 4
echo "4. Revisando orden 4:\n";
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = 4");
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);
echo "   ID: {$order['id']}\n";
echo "   Status: '" . ($order['status'] ?? 'NULL/EMPTY') . "'\n\n";

echo "5. Si el status está vacío, lo rellenaremos con 'enviado':\n";
if (empty($order['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'enviado' WHERE id = 4 AND (status = '' OR status IS NULL)");
    $stmt->execute();
    echo "   ✓ Orden 4 actualizada a 'enviado'\n";
} else {
    echo "   Status ya tiene valor: " . $order['status'] . "\n";
}

echo "\n✓ Migración completada\n";
?>