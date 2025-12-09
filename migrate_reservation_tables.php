<?php
// Script para migrar reservation_tables
try {
    $db = new \PDO('mysql:host=localhost;port=3307;dbname=grg_db', 'root', '');
    
    // Buscar todas las reservas que tienen table_id pero NO tienen entrada en reservation_tables
    $reservations = $db->query(
        "SELECT id, table_id FROM reservations 
         WHERE table_id IS NOT NULL 
         AND id NOT IN (SELECT DISTINCT reservation_id FROM reservation_tables)"
    )->fetchAll(\PDO::FETCH_ASSOC);
    
    echo "Encontradas " . count($reservations) . " reservas sin entrada en reservation_tables\n\n";
    
    $inserted = 0;
    foreach ($reservations as $res) {
        try {
            $stmt = $db->prepare(
                "INSERT INTO reservation_tables (reservation_id, table_id) VALUES (?, ?)"
            );
            $stmt->execute([$res['id'], $res['table_id']]);
            $inserted++;
            echo "✓ Reserva #{$res['id']} - Mesa {$res['table_id']}\n";
        } catch (\Exception $e) {
            echo "✗ Error en reserva #{$res['id']}: {$e->getMessage()}\n";
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Total insertados: $inserted\n";
    echo "Migración completada exitosamente!\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
