<?php
$sql = file_get_contents(__DIR__ . '/temp_add_reservation_tables.sql');
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=grg_db', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec($sql);
echo "done\n";
