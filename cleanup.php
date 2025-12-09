<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

echo "=== LIMPIANDO Y ARREGLANDO TABLA ORDERS ===\n\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

try {
    // Step 1: Update all empty/null status to 'enviado'
    echo "1. Llenando status vacíos con 'enviado'...\n";
    $stmt = $pdo->prepare("UPDATE orders SET status = 'enviado' WHERE status = '' OR status IS NULL");
    $stmt->execute();
    echo "   ✓ " . $stmt->rowCount() . " filas actualizadas\n\n";
    
    // Step 2: Verify
    echo "2. Verificando orden 4:\n";
    $order = $db->fetchOne("SELECT id, status FROM orders WHERE id = 4");
    echo "   ID: {$order['id']}\n";
    echo "   Status: '" . $order['status'] . "'\n\n";
    
    // Step 3: Now try a simple UPDATE
    echo "3. Probando UPDATE en orden 4:\n";
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute(['entregado', 4]);
    echo "   Affected rows: " . $stmt->rowCount() . "\n";
    
    // Step 4: Verify again
    echo "4. Verificando resultado:\n";
    $order = $db->fetchOne("SELECT id, status FROM orders WHERE id = 4");
    echo "   Status ahora: '" . $order['status'] . "'\n";
    
    if ($order['status'] === 'entregado') {
        echo "\n✓ ¡FUNCIONÓ! El status se actualizó a 'entregado'\n";
        
        // Reset for testing
        echo "\n5. Reseteando a 'enviado' para testing...\n";
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute(['enviado', 4]);
        echo "   ✓ Reset completado\n";
    } else {
        echo "\n✗ Aún no funciona. Status es: '" . $order['status'] . "'\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n✓ Script de limpieza completado\n";
?>
