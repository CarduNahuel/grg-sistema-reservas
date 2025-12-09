<?php
try {
    $db = new \PDO('mysql:host=localhost;port=3307;dbname=grg_db', 'root', '');
    $stmt = $db->query('SELECT id, table_number, position_x, position_y, zone, element_type FROM tables WHERE restaurant_id = 1');
    $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    
    echo "Total de mesas para restaurante 1: " . count($results) . "\n\n";
    
    foreach ($results as $row) {
        echo "ID: {$row['id']}, Número: {$row['table_number']}, X: {$row['position_x']}, Y: {$row['position_y']}, Zona: {$row['zone']}, Tipo: {$row['element_type']}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
