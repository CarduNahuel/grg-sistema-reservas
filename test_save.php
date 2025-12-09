<?php
// Test manual del guardado
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=grg_db', 'root', '');

// Verificar que la tabla reservation_tables existe
$res = $pdo->query("SELECT COUNT(*) as count FROM reservation_tables");
$data = $res->fetch(PDO::FETCH_ASSOC);
echo "Total de registros en reservation_tables: " . $data['count'] . "\n\n";

// Mostrar todos los registros
$res = $pdo->query("SELECT rt.*, t.table_number FROM reservation_tables rt JOIN tables t ON rt.table_id = t.id ORDER BY rt.created_at DESC LIMIT 10");
echo "Últimos 10 registros:\n";
print_r($res->fetchAll(PDO::FETCH_ASSOC));

echo "\n\nReservas con mesas asignadas:\n";
$res = $pdo->query("SELECT r.id, r.status, r.table_id, t.table_number FROM reservations r LEFT JOIN tables t ON r.table_id = t.id WHERE r.table_id IS NOT NULL LIMIT 10");
print_r($res->fetchAll(PDO::FETCH_ASSOC));
