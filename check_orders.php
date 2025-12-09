<?php
require_once __DIR__ . '/config/Database.php';
use Config\Database;

$db = Database::getInstance();
$pdo = $db->getConnection();

// Check the orders table structure
echo "=== ESTRUCTURA DE LA TABLA ORDERS ===\n";
$stmt = $pdo->prepare("DESCRIBE orders");
$stmt->execute();
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($columns as $col) {
    echo $col['Field'] . " (" . $col['Type'] . ") - Default: " . ($col['Default'] ?? 'NULL') . " - Null: " . $col['Null'] . "\n";
}

echo "\n=== ORDEN 4 ===\n";
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = 4");
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);
var_dump($order);

echo "\n=== TODAS LAS ÓRDENES ===\n";
$stmt = $pdo->prepare("SELECT id, status FROM orders LIMIT 10");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($orders as $o) {
    echo "ID: {$o['id']}, Status: '" . ($o['status'] ?? 'NULL') . "'\n";
}
?>