<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=grg_db', 'root', '');
$res = $pdo->query('SELECT * FROM reservation_tables LIMIT 10');
$data = $res->fetchAll(PDO::FETCH_ASSOC);
echo count($data) . " registros encontrados:\n";
print_r($data);

echo "\n\n=== Verificar reservations con table_id ===\n";
$res2 = $pdo->query('SELECT id, status, table_id FROM reservations WHERE table_id IS NOT NULL LIMIT 5');
print_r($res2->fetchAll(PDO::FETCH_ASSOC));
