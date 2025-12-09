<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=grg_db', 'root', '');

// Check roles table
$roles = $pdo->query('SELECT * FROM roles')->fetchAll(PDO::FETCH_ASSOC);
echo "Roles disponibles:\n";
print_r($roles);

// Check restaurants table structure
echo "\n\nColumnas en tabla restaurants:\n";
$cols = $pdo->query('DESCRIBE restaurants')->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
