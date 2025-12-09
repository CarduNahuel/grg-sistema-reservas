<?php ob_start(); ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
.plano-wrapper { display: flex; gap: 30px; max-width: 1400px; margin: 20px auto; padding: 20px; }
.plano-main { flex: 1; }
.plano-sidebar { width: 280px; }
.leyenda-item { display: flex; align-items: center; gap: 10px; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; }
.leyenda-color { width: 40px; height: 40px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 2px solid #666; }
.zone-btn { display: inline-block; margin: 4px 6px 0 0; padding: 6px 10px; border: 1px solid #dee2e6; border-radius: 6px; background: #fff; cursor: pointer; transition: all 0.2s; font-size: 13px; }
.zone-btn.active { background: #0ea5e9; color: white; border-color: #0ea5e9; font-weight: bold; }
.zone-btn:hover { background: #e7f3ff; border-color: #0ea5e9; }
.mesa-disponible { filter: none; }
.mesa-ocupada { filter: grayscale(0.7); opacity: 0.6; }
</style>

<div class="plano-wrapper">
    <div class="plano-main">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3>Plano - <?= htmlspecialchars($restaurant['name']) ?></h3>
                <p class="text-muted">Selecciona una mesa disponible</p>
            </div>
            <span id="zonaActualLabel" class="badge bg-primary" style="height: fit-content;">📍 General</span>
        </div>

        <div class="mb-2">
            <strong>Zonas:</strong>
            <button type="button" class="zone-btn active" data-zona="General" onclick="cambiarZona(event, 'General')">📍 General</button>
            <button type="button" class="zone-btn" data-zona="Terraza" onclick="cambiarZona(event, 'Terraza')">🌳 Terraza</button>
            <button type="button" class="zone-btn" data-zona="Jardin" onclick="cambiarZona(event, 'Jardin')">🌺 Jardín</button>
            <button type="button" class="zone-btn" data-zona="VIP" onclick="cambiarZona(event, 'VIP')">⭐ VIP</button>
            <button type="button" class="zone-btn" data-zona="Piso2" onclick="cambiarZona(event, 'Piso2')">🏢 Piso 2</button>
        </div>

        <div style="display: inline-grid; grid-template-columns: repeat(12, 60px); grid-template-rows: repeat(10, 60px); gap: 4px; padding: 20px; background: #f5f5f5; border-radius: 8px; margin-top: 10px;" id="grid">
            <?php
            $items = [];
            foreach ($tables as $t) {
                $col = (int)($t['position_x'] ?? 0);
                $row = (int)($t['position_y'] ?? 0);
                if ($col > 0 && $col <= 12 && $row > 0 && $row <= 10) {
                    $items[$row.'-'.$col] = $t;
                }
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

            for ($r = 1; $r <= 10; $r++) {
                for ($c = 1; $c <= 12; $c++) {
                    $key = $r.'-'.$c;
                    $item = $items[$key] ?? null;
                    $tipo = $item ? ($item['element_type'] ?? '') : '';
                    $id = $item['id'] ?? '';
                    $zona = $item['zone'] ?? 'General';
                    $icon = $icons[$tipo] ?? '';
                    $bgColor = $colors[$tipo] ?? '#f9f9f9';
                    $borderColor = $tipo ? '#666' : '#ccc';
                    $isMesa = $tipo === 'mesa';
                    $available = !$item ? false : ((int)($item['is_available'] ?? 1) === 1);
                    $cellClass = $isMesa ? ($available ? 'mesa-disponible' : 'mesa-ocupada') : '';
            ?>
                <div style="width: 60px; height: 60px; background: <?= $bgColor ?>; border: 2px solid <?= $borderColor ?>; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: <?= $isMesa ? 'pointer' : 'default' ?>; transition: all 0.1s; position: relative;" 
                     data-r="<?= $r ?>" data-c="<?= $c ?>" data-id="<?= $id ?>" data-tipo="<?= $tipo ?>" data-zona="<?= $zona ?>" data-numero="<?= $item['table_number'] ?? '' ?>" data-capacidad="<?= $item['capacity'] ?? '' ?>" data-disponible="<?= $available ? '1' : '0' ?>"
                     class="<?= $cellClass ?>"
                     onclick="handleClickCelda(event, <?= $isMesa ? 1 : 0 ?>)">
                    <?= $icon ?: '+' ?>
                </div>
            <?php } } ?>
        </div>
    </div>

    <div class="plano-sidebar">
        <div style="margin-bottom: 20px;">
            <h6 style="margin-bottom: 15px;">📋 Leyenda</h6>
            <div class="leyenda-item" style="background: #d1fae5; border-color: #10b981;">
                <div class="leyenda-color" style="background: #d1fae5; border-color: #10b981;">🪑</div>
                <div><strong>Mesa</strong><br><small>Disponible / Ocupada</small></div>
            </div>
            <div class="leyenda-item" style="background: #ddd6fe; border-color: #8b5cf6;">
                <div class="leyenda-color" style="background: #ddd6fe; border-color: #8b5cf6;">⬆️</div>
                <div><strong>Escalera</strong><br><small>Conecta zonas</small></div>
            </div>
            <div class="leyenda-item" style="background: #bfdbfe; border-color: #3b82f6;">
                <div class="leyenda-color" style="background: #bfdbfe; border-color: #3b82f6;">💧</div>
                <div><strong>Baño</strong><br><small>Servicio</small></div>
            </div>
            <div class="leyenda-item" style="background: #fed7aa; border-color: #f59e0b;">
                <div class="leyenda-color" style="background: #fed7aa; border-color: #f59e0b;">☕</div>
                <div><strong>Barra</strong><br><small>Mostrador</small></div>
            </div>
            <div class="leyenda-item" style="background: #fecaca; border-color: #ef4444;">
                <div class="leyenda-color" style="background: #fecaca; border-color: #ef4444;">🚪</div>
                <div><strong>Puerta</strong><br><small>Acceso/Conexión</small></div>
            </div>
            <div class="leyenda-item" style="background: #94a3b8; border-color: #64748b;">
                <div class="leyenda-color" style="background: #94a3b8; border-color: #64748b; color: white;">⬛</div>
                <div><strong>Pared</strong><br><small>Obstáculo</small></div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Mesa seleccionada</h6>
                <p id="infoMesa" class="text-muted mb-2">Ninguna mesa seleccionada</p>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary" id="btnReservar" disabled onclick="reservarMesa()">Reservar mesa</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let zonaActual = 'General';
let mesaSeleccionada = null;

function cambiarZona(event, zona) {
    zonaActual = zona;
    document.querySelectorAll('.zone-btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById('zonaActualLabel').textContent = event.target.textContent;
    filtrarPorZona();
}

function filtrarPorZona() {
    document.querySelectorAll('#grid [data-zona]').forEach(cell => {
        const zona = cell.dataset.zona || 'General';
        cell.style.display = (zona === zonaActual) ? 'flex' : 'none';
    });
}

function handleClickCelda(event, esMesa) {
    const cell = event.currentTarget;
    const disponible = cell.dataset.disponible === '1';
    const zona = cell.dataset.zona || 'General';
    if (!esMesa || !disponible) return;
    mesaSeleccionada = {
        id: cell.dataset.id,
        numero: cell.dataset.numero,
        capacidad: cell.dataset.capacidad,
        zona
    };
    document.getElementById('infoMesa').textContent = `Mesa ${mesaSeleccionada.numero || mesaSeleccionada.id} - Capacidad ${mesaSeleccionada.capacidad || 'N/D'} - Zona ${zona}`;
    document.getElementById('btnReservar').disabled = false;
}

function reservarMesa() {
    if (!mesaSeleccionada) return;
    alert(`Reservando mesa ${mesaSeleccionada.numero || mesaSeleccionada.id} en zona ${mesaSeleccionada.zona}`);
    // Aquí podrías redirigir a un formulario de reserva: window.location.href = `/reservar?table_id=${mesaSeleccionada.id}`;
}

// Inicial
filtrarPorZona();
</script>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
