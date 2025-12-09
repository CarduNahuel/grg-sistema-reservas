<?php
// cleanup_status.php
// Forzar que todas las órdenes con status vacío o NULL tengan 'enviado'


// Configura tus datos de conexión aquí

$host = 'localhost';
$port = '3307';
$db   = 'grg_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $sql = "UPDATE orders SET status = 'enviado' WHERE status IS NULL OR status = ''";
    $affected = $pdo->exec($sql);
    echo "Status reparados: $affected<br>\n";

    // Mostrar el status de la orden 4
    $stmt = $pdo->query("SELECT id, status FROM orders WHERE id = 4");
    $row = $stmt->fetch();
    if ($row) {
        echo "Orden 4: status = '{$row['status']}'<br>\n";
    } else {
        echo "Orden 4 no existe<br>\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
