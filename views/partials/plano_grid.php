<?php
/**
 * Partial para renderizar grillas de plano por zona
 * 
 * Variables esperadas:
 * - $tables: array de mesas (con position_x, position_y, zone, element_type, id, table_number, is_available)
 * - $gridId: string identificador único para los elementos HTML (ej: "reservationModal5")
 * - $selectable: bool (default true) - si las mesas son clickeables
 * - $assignedIds: array de IDs de mesas asignadas (para colorear en rosa)
 * - $occupiedIds: array de IDs de mesas ocupadas (para colorear en rojo)
 * - $onCellClick: string callback JS para click en mesa (ej: "selectTable(tableId)")
 */

// Valores por defecto
$selectable = $selectable ?? true;
$assignedIds = $assignedIds ?? [];
$occupiedIds = $occupiedIds ?? [];
$onCellClick = $onCellClick ?? '';

// Zonales dinámicas
$zones = array_values(array_filter(array_unique(array_map(function($t) {
    return $t['zone'] ?? 'General';
}, $tables ?? []))));
if (empty($zones)) { $zones = ['General']; }

$tablesByZone = [];
foreach ($tables ?? [] as $t) {
    $z = $t['zone'] ?? 'General';
    $tablesByZone[$z][] = $t;
}

$icons = ['mesa' => '🪑', 'escalera' => '⬆️', 'bano' => '💧', 'barra' => '☕', 'puerta' => '🚪', 'pared' => '⬛'];
$colors = [
    'mesa' => '#d1fae5',
    'escalera' => '#ddd6fe',
    'bano' => '#bfdbfe',
    'barra' => '#fed7aa',
    'puerta' => '#fecaca',
    'pared' => '#94a3b8'
];
?>

<style>
.zone-btn-<?= $gridId ?> {
    display: inline-block;
    margin: 4px 6px 0 0;
    padding: 6px 10px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
}
.zone-btn-<?= $gridId ?>.active {
    background: #0ea5e9;
    color: white;
    border-color: #0ea5e9;
    font-weight: bold;
}
.zone-btn-<?= $gridId ?>:hover {
    background: #e7f3ff;
    border-color: #0ea5e9;
}
.plano-grid-zone-<?= $gridId ?> {
    display: inline-grid;
    grid-template-columns: repeat(12, 50px);
    grid-template-rows: repeat(10, 50px);
    gap: 4px;
    padding: 12px;
    background: #f5f5f5;
    border-radius: 8px;
    margin-top: 8px;
}
.plano-cell-<?= $gridId ?> {
    width: 50px;
    height: 50px;
    border: 2px solid #ccc;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: all 0.1s;
    position: relative;
}
.plano-cell-<?= $gridId ?>[data-tipo="mesa"] {
    cursor: pointer;
}
.plano-cell-<?= $gridId ?>.mesa-seleccionada {
    background: #fde68a !important;
    border-color: #f59e0b !important;
    box-shadow: 0 0 0 2px #fcd34d inset;
}
.plano-cell-<?= $gridId ?>.mesa-ocupada {
    filter: grayscale(0.7);
    opacity: 0.6;
    cursor: not-allowed !important;
}
</style>

<div id="planoContainer-<?= $gridId ?>">
    <?php if (count($zones) > 1): ?>
        <div class="mb-2">
            <strong>Zonas:</strong>
            <div id="plano-zonas-<?= $gridId ?>">
                <?php foreach ($zones as $idx => $zone): ?>
                    <button type="button"
                            class="zone-btn-<?= $gridId ?> <?= $idx === 0 ? 'active' : '' ?>"
                            data-zona="<?= htmlspecialchars($zone) ?>"
                            onclick="planoCambiarZona_<?= $gridId ?>(event, '<?= htmlspecialchars($zone) ?>')">
                        <?= htmlspecialchars($zone) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php foreach ($zones as $idx => $zone):
        $items = [];
        foreach ($tablesByZone[$zone] ?? [] as $t) {
            $col = (int)($t['position_x'] ?? 0);
            $row = (int)($t['position_y'] ?? 0);
            if ($col > 0 && $col <= 12 && $row > 0 && $row <= 10) {
                $items[$row.'-'.$col] = $t;
            }
        }
    ?>
        <div class="plano-grid-zone-<?= $gridId ?>"
             id="planoGridZone-<?= $gridId ?>-<?= htmlspecialchars($zone) ?>"
             data-zone="<?= htmlspecialchars($zone) ?>"
             style="display: <?= $idx === 0 ? 'inline-grid' : 'none' ?>;">
            <?php
            for ($r = 1; $r <= 10; $r++) {
                for ($c = 1; $c <= 12; $c++) {
                $key = $r.'-'.$c;
                $item = $items[$key] ?? null;
                $tipo = $item['element_type'] ?? '';
                $id = $item['id'] ?? '';
                $cellZone = $item['zone'] ?? $zone;
                $icon = $icons[$tipo] ?? '';
                $bgColor = $colors[$tipo] ?? '#f9f9f9';
                $borderColor = $tipo ? '#666' : '#ccc';
                $isMesa = $tipo === 'mesa';
                $available = $item ? ((int)($item['is_available'] ?? 1) === 1) : false;
                $isAssigned = $id && in_array($id, $assignedIds);
                $isOccupied = $id && in_array($id, $occupiedIds);

                // Color por estado
                if ($isOccupied) {
                    $bgColor = '#f87171'; // rojo
                } elseif ($isAssigned) {
                    $bgColor = '#f8c2d1'; // rosa
                }

                $cellClass = 'plano-cell-' . $gridId;
                if ($isMesa && $isOccupied) {
                    $cellClass .= ' mesa-ocupada';
                }
                if ($isAssigned) {
                    $cellClass .= ' mesa-seleccionada';
                }

                $clickHandler = '';
                if ($isMesa && !$isOccupied && $selectable && $onCellClick) {
                    $clickHandler = 'onclick="' . str_replace('TABLEID', $id, $onCellClick) . '"';
                }
            ?>
            <div class="<?= $cellClass ?>"
                 style="background: <?= $bgColor ?>; border-color: <?= $borderColor ?>;"
                 data-r="<?= $r ?>" data-c="<?= $c ?>" data-id="<?= $id ?>" data-tipo="<?= $tipo ?>"
                 data-zona="<?= htmlspecialchars($cellZone ?: 'General') ?>" data-numero="<?= $item['table_number'] ?? '' ?>"
                 data-capacidad="<?= $item['capacity'] ?? '' ?>" data-disponible="<?= $available ? '1' : '0' ?>"
                 <?= $clickHandler ?> >
                <?= $icon ?: '+' ?>
            </div>
        <?php
            }
        }
        ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
// Funciones de zona para este plano
function planoCambiarZona_<?= $gridId ?>(event, zona) {
    document.querySelectorAll('#plano-zonas-<?= $gridId ?> .zone-btn-<?= $gridId ?>').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    document.querySelectorAll('.plano-grid-zone-<?= $gridId ?>').forEach(grid => {
        grid.style.display = (grid.dataset.zone === zona) ? 'inline-grid' : 'none';
    });
}
</script>
