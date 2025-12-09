<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

echo "=== DIAGNÓSTICO DE ÓRDENES ===\n\n";

$db = Database::getInstance();

// Check if table exists and has data
echo "1. Total de órdenes en la BD:\n";
$count = $db->fetchOne("SELECT COUNT(*) as total FROM orders");
echo "   Total: " . $count['total'] . "\n\n";

// List first 5 orders
echo "2. Primeras 5 órdenes:\n";
$orders = $db->fetchAll("SELECT id, status, created_at FROM orders LIMIT 5");
foreach ($orders as $order) {
    echo "   ID: {$order['id']}, Status: '" . ($order['status'] ?? 'NULL') . "', Created: {$order['created_at']}\n";
}

// Check if order 4 exists
echo "\n3. Buscando orden 4:\n";
$order4 = $db->fetchOne("SELECT * FROM orders WHERE id = 4");
if ($order4) {
    echo "   ✓ Orden 4 EXISTE\n";
    echo "   ID: {$order4['id']}\n";
    echo "   Status: '" . ($order4['status'] ?? 'NULL') . "'\n";
    echo "   Cart ID: {$order4['cart_id']}\n";
    echo "   User ID: {$order4['user_id']}\n";
} else {
    echo "   ✗ Orden 4 NO EXISTE\n";
}

// Direct MySQL query test
echo "\n4. Intentando UPDATE directo en orden 4:\n";
try {
    $pdo = $db->getConnection();
    
    $sql = "UPDATE orders SET status = 'TESTING' WHERE id = 4";
    echo "   Ejecutando: $sql\n";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute();
    echo "   Execute result: " . ($result ? 'true' : 'false') . "\n";
    echo "   Affected rows: " . $stmt->rowCount() . "\n";
    
    // Verify
    $verify = $db->fetchOne("SELECT id, status FROM orders WHERE id = 4");
    echo "   Status después: '" . ($verify['status'] ?? 'NULL') . "'\n";
    
    // Reset back
    $resetStmt = $pdo->prepare("UPDATE orders SET status = '' WHERE id = 4");
    $resetStmt->execute();
    
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n✓ Diagnóstico completado\n";
?>
