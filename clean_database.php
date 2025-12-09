<?php
/**
 * Script para limpiar datos corruptos de la base de datos
 * Mantiene la estructura pero elimina datos inconsistentes
 */

try {
    $db = new \PDO('mysql:host=localhost;port=3307;dbname=grg_db', 'root', '');
    $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    
    echo "🔄 Iniciando limpieza de base de datos...\n\n";
    
    // Desactivar foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Limpiar tablas
    echo "📋 Limpiando tablas...\n";
    
    $tables = [
        'reservation_tables',
        'reservations',
        'notifications',
        'audit_logs'
    ];
    
    foreach ($tables as $table) {
        try {
            $db->exec("TRUNCATE TABLE $table");
            echo "  ✓ {$table} limpiado\n";
        } catch (\Exception $e) {
            echo "  ⚠ {$table}: " . $e->getMessage() . "\n";
        }
    }
    
    // Reactivar foreign key checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "\n✅ Base de datos limpiada exitosamente!\n\n";
    
    // Mostrar conteos finales
    echo "📊 Estado final:\n";
    $counts = $db->query("
        SELECT 
            (SELECT COUNT(*) FROM reservations) as reservations_count,
            (SELECT COUNT(*) FROM reservation_tables) as reservation_tables_count,
            (SELECT COUNT(*) FROM notifications) as notifications_count,
            (SELECT COUNT(*) FROM users) as users_count,
            (SELECT COUNT(*) FROM restaurants) as restaurants_count,
            (SELECT COUNT(*) FROM tables) as tables_count
    ")->fetch(\PDO::FETCH_ASSOC);
    
    foreach ($counts as $key => $value) {
        echo "  • " . ucwords(str_replace('_', ' ', $key)) . ": $value\n";
    }
    
    echo "\n✨ ¡Listo! La base de datos está limpia y lista para usar.\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
