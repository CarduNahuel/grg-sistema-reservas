<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=grg_db', 'root', '');
$cols = $pdo->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
echo "Columns en tabla users:\n";
foreach ($cols as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
