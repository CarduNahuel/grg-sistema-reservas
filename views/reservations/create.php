<?php ob_start(); ?>

<?php
// Zonas disponibles según mesas del restaurante (para no mostrar zonas inexistentes)
$zones = array_values(array_filter(array_unique(array_map(function($t) {
    return $t['zone'] ?? 'General';
}, $tables ?? []))));
if (empty($zones)) { $zones = ['General']; }
?>

<style>
.zone-btn { display: inline-block; margin: 4px 6px 0 0; padding: 6px 10px; border: 1px solid #dee2e6; border-radius: 6px; background: #fff; cursor: pointer; transition: all 0.2s; font-size: 13px; }
.zone-btn.active { background: #0ea5e9; color: white; border-color: #0ea5e9; font-weight: bold; }
.zone-btn:hover { background: #e7f3ff; border-color: #0ea5e9; }
.mesa-disponible { filter: none; }
.mesa-ocupada { filter: grayscale(0.7); opacity: 0.6; }
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="mb-4">Crear Reserva en <?= htmlspecialchars($restaurant['name']) ?></h2>
                    
                    <form method="POST" action="/grg/reservations" id="reservationForm">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        
                        <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">

                        <div class="mb-3">
                            <label class="form-label">Zona preferida</label>
                            <input type="hidden" id="preferred_zone" name="preferred_zone" value="<?= htmlspecialchars($zones[0]) ?>">
                            <div class="d-flex flex-wrap gap-2" id="zoneButtons">
                                <?php foreach ($zones as $i => $zone): ?>
                                    <button type="button" class="zone-btn <?= $i === 0 ? 'active' : '' ?>" data-zone="<?= htmlspecialchars($zone) ?>">
                                        <?= htmlspecialchars($zone) ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">El restaurante asignará la mesa; esto es una preferencia.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reservation_date" class="form-label">Fecha *</label>
                                <input type="date" class="form-control" id="reservation_date" name="reservation_date" 
                                       min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="guest_count" class="form-label">Número de Personas *</label>
                                <input type="number" class="form-control" id="guest_count" name="guest_count" 
                                       min="1" max="20" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">Hora de Inicio *</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" 
                                       min="<?= date('H:i', strtotime($restaurant['opening_time'])) ?>" 
                                       max="<?= date('H:i', strtotime($restaurant['closing_time'])) ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">Hora de Fin *</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                        
                        <input type="hidden" name="table_id" id="table_id" value="">

                        <div class="mb-3">
                            <label class="form-label">Mesa preferida (opcional)</label>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <button type="button" class="btn btn-secondary btn-sm" id="checkAvailability">
                                    <i class="bi bi-arrow-clockwise"></i> Verificar Disponibilidad
                                </button>
                                <small class="text-muted">O selecciona directamente en el plano.</small>
                            </div>
                            <div id="availableTables" class="mt-2">
                                <p class="text-muted">Selecciona fecha y hora para ver mesas sugeridas, o elige una mesa en el plano.</p>
                            </div>
                        </div>

                        <?php
                        $gridId = 'createReservation' . $restaurant['id'];
                        $onCellClick = 'selectTableFromPlano_' . $gridId . '(TABLEID)';
                        include __DIR__ . '/../partials/plano_grid.php';
                        ?>
                        
                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Solicitudes Especiales</label>
                            <textarea class="form-control" id="special_requests" name="special_requests" 
                                      rows="3" placeholder="Alergias, cumpleaños, etc."></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                <i class="bi bi-calendar-check"></i> Confirmar Reserva
                            </button>
                            <a href="/grg/restaurants/<?= $restaurant['id'] ?>" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
const gridId = 'createReservation<?= $restaurant['id'] ?>';
const urlParams = new URLSearchParams(window.location.search);
const preselectedTableId = urlParams.get('table_id');
const tableInput = document.getElementById('table_id');
const submitBtn = document.getElementById('submitBtn');
const preferredZoneInput = document.getElementById('preferred_zone');
const zoneButtons = document.querySelectorAll('#zoneButtons .zone-btn');
const specialRequestsInput = document.getElementById('special_requests');
const draftKey = `reservation_draft_sr_<?= $restaurant['id'] ?>`;
let planoMesaSeleccionada = null;
let planoCeldaSeleccionada = null;

function setPreferredZone(zone) {
    preferredZoneInput.value = zone;
    zoneButtons.forEach(btn => {
        if (btn.dataset.zone === zone) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    // Sincroniza la grilla de zonas del plano
    document.querySelectorAll('#plano-zonas-' + gridId + ' .zone-btn-' + gridId).forEach(btn => {
        if (btn.dataset.zona === zone) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    document.querySelectorAll('.plano-grid-zone-' + gridId).forEach(grid => {
        grid.style.display = (grid.dataset.zone === zone) ? 'inline-grid' : 'none';
    });
    updatePlanoColors();
}

zoneButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        setPreferredZone(btn.dataset.zone);
    });
});

function setTableSelection(tableId, infoText = '') {
    tableInput.value = tableId || '';
    if (infoText) {
        document.getElementById('availableTables').innerHTML = `<div class="alert alert-info mb-2">${infoText}</div>`;
    }
    submitBtn.disabled = false;
}

function selectTableFromPlano_createReservation<?= $restaurant['id'] ?>(tableId) {
    const cell = document.querySelector(`[data-id="${tableId}"]`);
    if (cell && cell.dataset.tipo === 'mesa' && cell.dataset.disponible === '1') {
        if (planoCeldaSeleccionada) {
            planoCeldaSeleccionada.classList.remove('mesa-seleccionada');
        }
        cell.classList.add('mesa-seleccionada');
        planoCeldaSeleccionada = cell;
        
        const zona = cell.dataset.zona || 'General';
        planoMesaSeleccionada = {
            id: cell.dataset.id,
            numero: cell.dataset.numero,
            capacidad: cell.dataset.capacidad,
            zona
        };
        
        setTableSelection(planoMesaSeleccionada.id, `Mesa preferida: ${planoMesaSeleccionada.numero || planoMesaSeleccionada.id} (zona ${zona})`);
    }
}

// Función para actualizar los colores del plano en tiempo real
function updatePlanoColors() {
    const date = document.getElementById('reservation_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const restaurantId = <?= $restaurant['id'] ?>;
    
    if (!date || !startTime || !endTime) {
        return;
    }
    
    fetch('/grg/restaurants/availability', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `restaurant_id=${restaurantId}&date=${date}&start_time=${startTime}&end_time=${endTime}`
    })
    .then(response => response.json())
    .then(data => {
        console.log('Disponibilidad recibida:', data);
        
        // Obtener IDs disponibles
        const availableIds = new Set(data.tables?.map(t => t.id.toString()) || []);
        
        // Actualizar todos los elementos del plano de esta clase
        document.querySelectorAll(`.plano-cell-${gridId}[data-tipo="mesa"]`).forEach(cell => {
            const cellId = cell.getAttribute('data-id');
            
            // Remover clases previas de ocupación
            cell.classList.remove('mesa-disponible', 'mesa-ocupada');
            
            // Aplicar la clase correcta
            if (availableIds.has(cellId)) {
                // Mesa disponible: color normal
                cell.classList.add('mesa-disponible');
                cell.setAttribute('data-disponible', '1');
                cell.style.filter = 'none';
                cell.style.opacity = '1';
            } else {
                // Mesa ocupada: gris
                cell.classList.add('mesa-ocupada');
                cell.setAttribute('data-disponible', '0');
                cell.style.filter = 'grayscale(0.7)';
                cell.style.opacity = '0.6';
            }
        });
    })
    .catch(error => {
        console.error('Error actualizando plano:', error);
    });
}

// Check availability keeps using server suggestions but updates hidden table input
document.getElementById('checkAvailability').addEventListener('click', function() {
    const date = document.getElementById('reservation_date').value;
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    const restaurantId = <?= $restaurant['id'] ?>;
    
    if (!date || !startTime || !endTime) {
        alert('Por favor completa fecha y horario');
        return;
    }
    
    fetch('/grg/restaurants/availability', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `restaurant_id=${restaurantId}&date=${date}&start_time=${startTime}&end_time=${endTime}`
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('availableTables');
        if (data.success && data.tables.length > 0) {
            let html = '<div class="row">';
            html += `
                <div class="col-md-12 mb-2">
                    <div class="form-check card p-3">
                        <input class="form-check-input" type="radio" name="table_id_option" id="table_auto" value="" checked>
                        <label class="form-check-label" for="table_auto">
                            <strong>Que el restaurante asigne la mesa</strong><br>
                            <small>Tomaremos tu zona como preferencia.</small>
                        </label>
                    </div>
                </div>`;

            data.tables.forEach(table => {
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="form-check card p-3">
                            <input class="form-check-input" type="radio" name="table_id_option" id="table_${table.id}" value="${table.id}">
                            <label class="form-check-label" for="table_${table.id}">
                                <strong>Mesa ${table.table_number}</strong> - ${table.area}<br>
                                <small>Capacidad: ${table.capacity} personas</small>
                            </label>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;

            // Sync radio selection to hidden input
            container.querySelectorAll('input[name="table_id_option"]').forEach(r => {
                r.addEventListener('change', () => {
                    setTableSelection(r.value || '', r.value ? `Mesa preferida: ${r.nextElementSibling?.querySelector('strong')?.textContent || ''}` : 'Asignación automática por el restaurante');
                });
            });

            // default auto assign
            setTableSelection('', 'Asignación automática por el restaurante');
        } else {
            container.innerHTML = '<div class="alert alert-warning">No hay mesas disponibles para este horario.</div>';
            // No selection; keep current selection if existed
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al verificar disponibilidad');
    });
});

// Auto-calculate end time (2 hours after start)
document.getElementById('start_time').addEventListener('change', function() {
    const start = this.value;
    if (start) {
        const [hours, minutes] = start.split(':');
        const endHours = (parseInt(hours, 10) + 2) % 24;
        const endTime = `${String(endHours).padStart(2, '0')}:${minutes}`;
        document.getElementById('end_time').value = endTime;
    }
    // Actualizar plano cuando cambia la hora
    updatePlanoColors();
});

// Actualizar plano cuando cambia la hora de fin
document.getElementById('end_time').addEventListener('change', function() {
    updatePlanoColors();
});

// Actualizar plano cuando cambia la fecha
document.getElementById('reservation_date').addEventListener('change', function() {
    updatePlanoColors();
});

// Guardar/recuperar borrador de Solicitudes Especiales para evitar pérdida
if (specialRequestsInput) {
    // Restaurar borrador si existe
    const draft = localStorage.getItem(draftKey);
    if (draft) {
        specialRequestsInput.value = draft;
    }
    // Guardar en tiempo real
    specialRequestsInput.addEventListener('input', () => {
        localStorage.setItem(draftKey, specialRequestsInput.value);
    });
}

// Preselección desde la URL
document.addEventListener('DOMContentLoaded', () => {
    // Establecer hora default: hora actual + 2 horas
    const now = new Date();
    const currentHours = now.getHours();
    const currentMinutes = String(now.getMinutes()).padStart(2, '0');
    const defaultHours = String((currentHours + 2) % 24).padStart(2, '0');
    const defaultStartTime = `${defaultHours}:${currentMinutes}`;
    const defaultEndHours = String((currentHours + 4) % 24).padStart(2, '0');
    const defaultEndTime = `${defaultEndHours}:${currentMinutes}`;
    
    document.getElementById('start_time').value = defaultStartTime;
    document.getElementById('end_time').value = defaultEndTime;
    
    // Fijar zona por defecto y refrescar plano
    setPreferredZone(preferredZoneInput.value || 'General');
    // Actualizar plano con defaults - esperar a que el plano esté renderizado
    setTimeout(() => {
        updatePlanoColors();
    }, 400);
    
    if (preselectedTableId) {
        const cell = document.querySelector(`[data-id="${preselectedTableId}"]`);
        if (cell) {
            const zona = cell.dataset.zona || 'General';
            // Cambiar a la zona correcta
            document.querySelectorAll(`#plano-zonas-${gridId} .zone-btn-${gridId}`).forEach(btn => {
                if (btn.dataset.zona === zona) {
                    btn.click();
                }
            });
            // Pequeña pausa para que se renderice la zona
            setTimeout(() => {
                selectTableFromPlano_createReservation<?= $restaurant['id'] ?>(preselectedTableId);
            }, 100);
        } else {
            setTableSelection(preselectedTableId, 'Mesa preseleccionada desde el plano.');
        }
    }
});
</script>
<?php $scripts = ob_get_clean(); ?>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
