<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

echo "=== VERIFICANDO TRIGGERS ===\n\n";

$db = Database::getInstance();
$pdo = $db->getConnection();

// Check for triggers
$stmt = $pdo->prepare("SHOW TRIGGERS");
$stmt->execute();
$triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($triggers)) {
    echo "No hay triggers definidos\n\n";
} else {
    echo "Triggers encontrados:\n";
    foreach ($triggers as $trigger) {
        echo "- {$trigger['Trigger']}: {$trigger['Event']} on {$trigger['Table']}\n";
        echo "  Statement: {$trigger['Statement']}\n\n";
    }
}

// Check the actual column definition
echo "=== ESTRUCTURA DE TABLA ORDERS ===\n\n";
$stmt = $pdo->prepare("DESCRIBE orders");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    if ($col['Field'] === 'status') {
        echo "Field: {$col['Field']}\n";
        echo "Type: {$col['Type']}\n";
        echo "Null: {$col['Null']}\n";
        echo "Key: {$col['Key']}\n";
        echo "Default: '" . ($col['Default'] ?? 'NULL') . "'\n";
        echo "Extra: {$col['Extra']}\n\n";
    }
}

// Try a direct test update
echo "=== PRUEBA DIRECTA DE UPDATE ===\n\n";
echo "Orden 2 antes:\n";
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = 2");
$stmt->execute();
$before = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($before);

echo "\nEjecutando UPDATE...\n";
$stmt = $pdo->prepare("UPDATE orders SET status = 'cancelado' WHERE id = 2");
$stmt->execute();
echo "Filas afectadas: " . $stmt->rowCount() . "\n";

echo "\nOrden 2 después:\n";
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = 2");
$stmt->execute();
$after = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($after);

?>
