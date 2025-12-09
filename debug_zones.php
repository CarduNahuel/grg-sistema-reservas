<?php
// Simular la lógica de la partial
$db = new \PDO('mysql:host=localhost;port=3307;dbname=grg_db', 'root', '');
$stmt = $db->query('SELECT * FROM tables WHERE restaurant_id = 1');
$tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);

echo "Total de elementos: " . count($tables) . "\n\n";

// Extraer zonas únicas (igual que en la partial)
$zones = array_values(array_filter(array_unique(array_map(function($t) {
    return $t['zone'] ?? 'General';
}, $tables ?? []))));

if (empty($zones)) { $zones = ['General']; }

echo "Zonas encontradas: " . count($zones) . "\n";
foreach ($zones as $idx => $zone) {
    echo "  $idx: $zone\n";
}

// Agrupar por zona
$tablesByZone = [];
foreach ($tables ?? [] as $t) {
    $z = $t['zone'] ?? 'General';
    $tablesByZone[$z][] = $t;
}

echo "\nElementos por zona:\n";
foreach ($tablesByZone as $zone => $items) {
    echo "  $zone: " . count($items) . " elementos\n";
}

// Simular el renderizado del primer grid
echo "\nPrimer grid (zona '{$zones[0]}'):\n";
$items = [];
foreach ($tablesByZone[$zones[0]] ?? [] as $t) {
    $col = (int)($t['position_x'] ?? 0);
    $row = (int)($t['position_y'] ?? 0);
    if ($col > 0 && $col <= 12 && $row > 0 && $row <= 10) {
        $items[$row.'-'.$col] = $t;
        echo "  Posición $row-$col: {$t['table_number']} ({$t['element_type']})\n";
    }
}
?>
