<?php
require 'bootstrap/app.php';

// Configurar database
$config = require 'config/database.php';
$db = \App\Services\Database::getInstance($config);

// Ver todas las reservas
$reservations = $db->fetchAll("SELECT id, status, table_id FROM reservations");
echo "=== RESERVAS ===\n";
foreach ($reservations as $r) {
    echo "Reserva #{$r['id']}: Status={$r['status']}, table_id={$r['table_id']}\n";
    
    // Ver mesas asignadas
    $assigned = $db->fetchAll(
        "SELECT t.id, t.table_number, t.capacity 
         FROM reservation_tables rt
         JOIN tables t ON rt.table_id = t.id
         WHERE rt.reservation_id = ?",
        [$r['id']]
    );
    
    if (!empty($assigned)) {
        echo "  Mesas asignadas:\n";
        foreach ($assigned as $a) {
            echo "    - Mesa #{$a['id']} (Número: {$a['table_number']}, Cap: {$a['capacity']})\n";
        }
    } else {
        echo "  Sin mesas asignadas\n";
    }
}
