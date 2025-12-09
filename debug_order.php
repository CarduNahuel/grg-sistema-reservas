<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Consultar la orden 4
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([4]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

echo "=== ORDEN ACTUAL ===\n";
var_dump($order);

echo "\n=== INTENTANDO ACTUALIZAR A 'entregado' ===\n";

$pdo->beginTransaction();
$stmt = $pdo->prepare("UPDATE orders SET status = :status WHERE id = :id");
$result = $stmt->execute([':status' => 'entregado', ':id' => 4]);

echo "Execute result: " . ($result ? 'true' : 'false') . "\n";
echo "Affected rows: " . $stmt->rowCount() . "\n";

$pdo->commit();

echo "\n=== VERIFICANDO DESPUÉS DE UPDATE ===\n";
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([4]);
$updated = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($updated);

echo "\n=== ESTRUCTURA DE LA TABLA ===\n";
$stmt = $pdo->prepare("SHOW CREATE TABLE orders");
$stmt->execute();
$create = $stmt->fetch(PDO::FETCH_ASSOC);
echo $create['Create Table'] . "\n";

echo "\n=== TRIGGERS ===\n";
$stmt = $pdo->prepare("SHOW TRIGGERS LIKE 'orders'");
$stmt->execute();
$triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($triggers)) {
    echo "No triggers found\n";
} else {
    var_dump($triggers);
}
?>