<?php ob_start(); ?>

<style>
.plano-wrapper { display: flex; gap: 30px; max-width: 1400px; margin: 20px auto; padding: 20px; }
.plano-main { flex: 1; }
.plano-sidebar { width: 280px; }
.leyenda-item { display: flex; align-items: center; gap: 10px; padding: 10px; margin-bottom: 10px; border-radius: 6px; border: 1px solid #ddd; font-size: 13px; }
.leyenda-color { width: 40px; height: 40px; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 2px solid #666; }
.zones-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 20px; border: 1px solid #dee2e6; }
.zone-btn { display: block; width: 100%; text-align: left; padding: 8px 12px; margin-bottom: 8px; border: 1px solid #dee2e6; border-radius: 4px; background: #fff; cursor: pointer; transition: all 0.2s; font-size: 13px; }
.zone-btn:hover { background: #e7f3ff; border-color: #0ea5e9; }
.zone-btn.active { background: #0ea5e9; color: white; border-color: #0ea5e9; font-weight: bold; }
.conexion-hint { background: #fff3cd; border: 1px solid #ffc107; padding: 10px; border-radius: 4px; margin-top: 10px; font-size: 12px; }
</style>

<div class="plano-wrapper">
    <div class="plano-main">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3>Configurar Plano - <?= htmlspecialchars($restaurant['name']) ?></h3>
                <p class="text-muted">Haz click en cualquier celda para agregar o editar elementos</p>
            </div>
            <span id="zonaActualLabel" class="badge bg-primary" style="height: fit-content;">📍 General</span>
        </div>

        <div style="display: inline-grid; grid-template-columns: repeat(12, 60px); grid-template-rows: repeat(10, 60px); gap: 4px; padding: 20px; background: #f5f5f5; border-radius: 8px; margin-top: 20px;" id="grid">
            <?php
            $items = [];
            // Obtener zona actual desde URL o usar General por defecto
            $zonaActual = 'General';
            if (isset($_GET['zona'])) {
                $zonaActual = htmlspecialchars($_GET['zona']);
            }
            
            foreach ($tables as $t) {
                $col = (int)($t['position_x'] ?? 0);
                $row = (int)($t['position_y'] ?? 0);
                $zona = $t['zone'] ?? 'General';
                // Solo incluir elementos de la zona actual
                if ($col > 0 && $col <= 12 && $row > 0 && $row <= 10 && $zona === $zonaActual) {
                    $items[$row.'-'.$col] = $t;
                }
            }

            for ($r = 1; $r <= 10; $r++) {
                for ($c = 1; $c <= 12; $c++) {
                    $key = $r.'-'.$c;
                    $item = $items[$key] ?? null;
                    $tipo = $item ? ($item['element_type'] ?? 'mesa') : '';
                    $id = $item ? ($item['id'] ?? '') : '';
                    $zona = $item ? ($item['zone'] ?? 'General') : '';
                    
                    $icons = ['mesa' => '🪑', 'escalera' => '⬆️', 'bano' => '💧', 'barra' => '☕', 'puerta' => '🚪', 'pared' => '⬛'];
                    $icon = $icons[$tipo] ?? '+';
                    
                    $colors = [
                        'mesa' => '#d1fae5',
                        'escalera' => '#ddd6fe',
                        'bano' => '#bfdbfe',
                        'barra' => '#fed7aa',
                        'puerta' => '#fecaca',
                        'pared' => '#94a3b8'
                    ];
                    $bgColor = $colors[$tipo] ?? '#f9f9f9';
                    $borderColor = $tipo ? '#666' : '#ccc';
            ?>
                <div style="width: 60px; height: 60px; background: <?= $bgColor ?>; border: 2px solid <?= $borderColor ?>; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 20px; cursor: pointer; transition: all 0.1s; position: relative;" 
                     data-r="<?= $r ?>" data-c="<?= $c ?>" data-id="<?= $id ?>" data-tipo="<?= $tipo ?>" data-zona="<?= $zona ?>" 
                     onclick="abrirModal(<?= $r ?>, <?= $c ?>)"
                     title="<?= $zona ?><?= $item ? ' - ' . ($item['table_number'] ?? '') : '' ?>">
                    <?= $icon ?>
                </div>
            <?php } } ?>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="plano-sidebar">
        <div style="margin-bottom: 20px;">
            <h6 style="margin-bottom: 15px;">📋 Leyenda</h6>
            <div class="leyenda-item" style="background: #d1fae5; border-color: #10b981;">
                <div class="leyenda-color" style="background: #d1fae5; border-color: #10b981;">🪑</div>
                <div><strong>Mesa</strong><br><small>Para clientes</small></div>
            </div>
            <div class="leyenda-item" style="background: #ddd6fe; border-color: #8b5cf6;">
                <div class="leyenda-color" style="background: #ddd6fe; border-color: #8b5cf6;">⬆️</div>
                <div><strong>Escalera</strong><br><small>Conecta pisos</small></div>
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

        <hr>

        <div class="zones-box">
            <h6 class="mb-3">📍 Áreas/Zonas</h6>
            <div class="mb-3" id="zonasContainer">
                <?php
                // Obtener zonas únicas del restaurante
                $zonas = [];
                foreach ($tables as $t) {
                    $zona = $t['zone'] ?? 'General';
                    if (!in_array($zona, $zonas)) {
                        $zonas[] = $zona;
                    }
                }
                // Siempre incluir General aunque no tenga mesas
                if (empty($zonas)) {
                    $zonas = ['General'];
                } elseif (!in_array('General', $zonas)) {
                    array_unshift($zonas, 'General');
                }
                
                foreach ($zonas as $index => $zona):
                    // Marcar como activo la zona desde la URL o General por defecto
                    $isActive = ($zona === $zonaActual) ? 'active' : '';
                    $icons = ['General' => '📍', 'Terraza' => '🌳', 'Jardin' => '🌺', 'VIP' => '⭐', 'Piso2' => '🏢'];
                    $icon = $icons[$zona] ?? '📍';
                ?>
                    <button type="button" class="zone-btn <?= $isActive ?>" data-zona="<?= htmlspecialchars($zona) ?>" onclick="cambiarZona(event, '<?= htmlspecialchars($zona) ?>')"><?= $icon ?> <?= htmlspecialchars($zona) ?></button>
                <?php endforeach; ?>
                <button type="button" class="zone-btn" onclick="agregarZona()" style="background: #f0f0f0;">➕ Nueva zona</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar/editar elementos -->
<div class="modal fade" id="elementoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Agregar Elemento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modalRow">
                <input type="hidden" id="modalCol">
                
                <div class="mb-3">
                    <label>Zona</label>
                    <div class="form-control" style="background: #f8f9fa;">📍 <span id="modalZonaLabel">General</span></div>
                    <input type="hidden" id="modalZona" value="General">
                </div>

                <div class="mb-3">
                    <label>¿Qué es?</label>
                    <select id="modalTipo" class="form-select" onchange="cambiarTipo()">
                        <option value="">Vacío</option>
                        <option value="mesa">🪑 Mesa</option>
                        <option value="escalera">⬆️ Escalera</option>
                        <option value="bano">💧 Baño</option>
                        <option value="barra">☕ Barra</option>
                        <option value="puerta">🚪 Puerta</option>
                        <option value="pared">⬛ Pared</option>
                    </select>
                </div>

                <!-- Campos para Mesa -->
                <div id="mesaFields" style="display: none;">
                    <div class="mb-3">
                        <label>Número de mesa (opcional)</label>
                        <input type="text" id="modalNumero" class="form-control" placeholder="A1, 1, etc.">
                    </div>
                    <div class="mb-3">
                        <label>Capacidad</label>
                        <input type="number" id="modalCapacidad" class="form-control" value="4" min="1">
                    </div>
                </div>

                <!-- Campos para Escalera/Puerta -->
                <div id="conexionFields" style="display: none;">
                    <div class="alert alert-info" role="alert">
                        <strong>Conectar con otra zona</strong><br>
                        <small>Esta escalera/puerta conecta con otra área del restaurante</small>
                    </div>
                    <div class="mb-3">
                        <label>Conecta con la zona:</label>
                        <select id="modalZonaConexion" class="form-select">
                            <option value="">Seleccionar zona destino...</option>
                            <?php
                            // Obtener zonas únicas del restaurante para el select
                            $zonasSelect = [];
                            foreach ($tables as $t) {
                                $zona = $t['zone'] ?? 'General';
                                if (!in_array($zona, $zonasSelect)) {
                                    $zonasSelect[] = $zona;
                                }
                            }
                            if (empty($zonasSelect)) {
                                $zonasSelect = ['General'];
                            } elseif (!in_array('General', $zonasSelect)) {
                                array_unshift($zonasSelect, 'General');
                            }
                            
                            foreach ($zonasSelect as $zona):
                                $icons = ['General' => '📍', 'Terraza' => '🌳', 'Jardin' => '🌺', 'VIP' => '⭐', 'Piso2' => '🏢'];
                                $icon = $icons[$zona] ?? '📍';
                            ?>
                                <option value="<?= htmlspecialchars($zona) ?>"><?= $icon ?> <?= htmlspecialchars($zona) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Descripción (opcional)</label>
                        <input type="text" id="modalDescripcion" class="form-control" placeholder="Ej: Escalera sur, Puerta trasera">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="btnBorrar" style="display: none;" onclick="borrarElemento()">Borrar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarElemento()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Leer zona actual desde URL o localStorage
const urlParams = new URLSearchParams(window.location.search);
let zonaActual = urlParams.get('zona') || localStorage.getItem('plano_zona_actual') || 'General';
let modalObj; // Declarar primero

// Actualizar botón activo al cargar
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar modal después de que el DOM esté listo
    modalObj = new bootstrap.Modal(document.getElementById('elementoModal'));
    
    const zonasContainer = document.getElementById('zonasContainer');
    if (zonasContainer) {
        const buttons = zonasContainer.querySelectorAll('.zone-btn:not([onclick*="agregarZona"])');
        buttons.forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.zona === zonaActual) {
                btn.classList.add('active');
            }
        });
        document.getElementById('zonaActualLabel').textContent = '📍 ' + zonaActual;
    }
});

function cambiarZona(event, zona) {
    event.preventDefault();
    event.stopPropagation();
    
    zonaActual = zona;
    
    // Actualizar botones activos
    document.querySelectorAll('.zone-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    // Actualizar label
    document.getElementById('zonaActualLabel').textContent = '📍 ' + zona;
    
    // Actualizar modal
    document.getElementById('modalZona').value = zonaActual;
    document.getElementById('modalZonaLabel').textContent = zonaActual;
    
    // Guardar en localStorage
    localStorage.setItem('plano_zona_actual', zona);
    
    // Recargar solo el grid con AJAX en lugar de recargar la página
    const restaurantId = '<?= $restaurant['id'] ?>';
    fetch(`/grg/owner/restaurants/${restaurantId}/plano?zona=${encodeURIComponent(zona)}`)
        .then(res => res.text())
        .then(html => {
            // Extraer solo el grid del HTML
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('grid');
            if (newGrid) {
                document.getElementById('grid').innerHTML = newGrid.innerHTML;
            }
        })
        .catch(err => console.error('Error al cambiar zona:', err));
}

function agregarZona() {
    const nombre = prompt('Nombre de la nueva zona:');
    if (!nombre || nombre.trim() === '') return;
    
    const nombreTrim = nombre.trim();
    
    // Verificar si la zona ya existe
    const zonasContainer = document.getElementById('zonasContainer');
    const existingButtons = zonasContainer.querySelectorAll('.zone-btn:not([onclick*="agregarZona"])');
    for (let btn of existingButtons) {
        if (btn.dataset.zona === nombreTrim) {
            alert('Esta zona ya existe');
            return;
        }
    }
    
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'zone-btn';
    btn.setAttribute('data-zona', nombreTrim);
    btn.textContent = '📍 ' + nombreTrim;
    btn.onclick = (e) => cambiarZona(e, nombreTrim);
    
    const nuevoBtn = zonasContainer.querySelector('button[onclick*="agregarZona"]');
    zonasContainer.insertBefore(btn, nuevoBtn);
    
    // Agregar también en select del modal
    const select = document.getElementById('modalZonaConexion');
    const opt = document.createElement('option');
    opt.value = nombreTrim;
    opt.textContent = '📍 ' + nombreTrim;
    select.appendChild(opt);
    
    // Mostrar mensaje de éxito
    alert('Zona "' + nombreTrim + '" creada. Ahora puedes agregar elementos en esta zona.');
}

function abrirModal(row, col) {
    const cell = document.querySelector(`[data-r="${row}"][data-c="${col}"]`);
    const id = cell.dataset.id;
    const tipo = cell.dataset.tipo;
    const zona = cell.dataset.zona;
    
    document.getElementById('modalRow').value = row;
    document.getElementById('modalCol').value = col;
    document.getElementById('modalZona').value = zona || zonaActual;
    document.getElementById('modalZonaLabel').textContent = zona || zonaActual;
    document.getElementById('modalTipo').value = tipo || '';
    document.getElementById('btnBorrar').style.display = id ? 'inline-block' : 'none';
    
    cambiarTipo();
    
    if (id && tipo) {
        document.getElementById('modalTitle').textContent = 'Editar Elemento';
    } else {
        document.getElementById('modalTitle').textContent = 'Agregar Elemento';
    }
    
    modalObj.show();
}

function cambiarTipo() {
    const tipo = document.getElementById('modalTipo').value;
    const mesaFields = document.getElementById('mesaFields');
    const conexionFields = document.getElementById('conexionFields');
    
    if (tipo === 'mesa') {
        mesaFields.style.display = 'block';
        conexionFields.style.display = 'none';
    } else if (tipo === 'escalera' || tipo === 'puerta') {
        mesaFields.style.display = 'none';
        conexionFields.style.display = 'block';
    } else {
        mesaFields.style.display = 'none';
        conexionFields.style.display = 'none';
    }
}

function guardarElemento() {
    const row = parseInt(document.getElementById('modalRow').value);
    const col = parseInt(document.getElementById('modalCol').value);
    const tipo = document.getElementById('modalTipo').value;
    const zona = document.getElementById('modalZona').value;
    
    if (!tipo) {
        borrarElemento();
        return;
    }
    
    const formData = new FormData();
    formData.append('row', row);
    formData.append('col', col);
    formData.append('element_type', tipo);
    formData.append('zone', zona);

    if (tipo === 'mesa') {
        formData.append('table_number', document.getElementById('modalNumero').value || '');
        formData.append('capacity', document.getElementById('modalCapacidad').value || 4);
    } else if (tipo === 'escalera' || tipo === 'puerta') {
        formData.append('connected_zone', document.getElementById('modalZonaConexion').value || '');
        formData.append('description', document.getElementById('modalDescripcion').value || '');
    }

    const restaurantId = '<?= $restaurant['id'] ?>';
    fetch(`/grg/owner/restaurants/${restaurantId}/plano/save`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': '<?= \App\Services\CSRFProtection::generateToken() ?>' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'desconocido'));
        }
    })
    .catch(err => alert('Error de red: ' + err));
}

function borrarElemento() {
    const row = parseInt(document.getElementById('modalRow').value);
    const col = parseInt(document.getElementById('modalCol').value);
    const cell = document.querySelector(`[data-r="${row}"][data-c="${col}"]`);
    const id = cell.dataset.id;
    
    if (!id) {
        modalObj.hide();
        return;
    }

    if (!confirm('¿Borrar este elemento?')) return;
    
    const formData = new FormData();
    formData.append('table_id', id);

    const restaurantId = '<?= $restaurant['id'] ?>';
    fetch(`/grg/owner/restaurants/${restaurantId}/plano/delete`, {
        method: 'POST',
        headers: { 'X-CSRF-Token': '<?= \App\Services\CSRFProtection::generateToken() ?>' },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'desconocido'));
        }
    });
}

// === DRAG & DROP ===
let draggedElement = null;

document.addEventListener('DOMContentLoaded', () => {
    const cells = document.querySelectorAll('#grid > div');
    
    cells.forEach(cell => {
        const hasElement = cell.dataset.id && cell.dataset.tipo;
        
        if (hasElement) {
            // Hacer draggable los elementos existentes
            cell.setAttribute('draggable', 'true');
            cell.style.cursor = 'move';
            
            cell.addEventListener('dragstart', (e) => {
                draggedElement = {
                    id: cell.dataset.id,
                    tipo: cell.dataset.tipo,
                    oldRow: cell.dataset.r,
                    oldCol: cell.dataset.c
                };
                cell.style.opacity = '0.4';
            });
            
            cell.addEventListener('dragend', (e) => {
                cell.style.opacity = '1';
            });
        }
        
        // Todas las celdas pueden recibir drops
        cell.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!draggedElement) return;
            cell.style.backgroundColor = '#e0f2fe';
        });
        
        cell.addEventListener('dragleave', (e) => {
            if (!draggedElement) return;
            // Restaurar color original según tipo
            const originalColors = {
                'mesa': '#d1fae5',
                'escalera': '#ddd6fe',
                'bano': '#bfdbfe',
                'barra': '#fed7aa',
                'puerta': '#fecaca',
                'pared': '#94a3b8'
            };
            const tipo = cell.dataset.tipo;
            cell.style.backgroundColor = originalColors[tipo] || '#f9f9f9';
        });
        
        cell.addEventListener('drop', (e) => {
            e.preventDefault();
            if (!draggedElement) return;
            
            const newRow = cell.dataset.r;
            const newCol = cell.dataset.c;
            
            // No hacer nada si es la misma celda
            if (newRow === draggedElement.oldRow && newCol === draggedElement.oldCol) {
                cell.style.backgroundColor = '';
                return;
            }
            
            // No permitir drop si la celda está ocupada
            if (cell.dataset.id && cell.dataset.id !== draggedElement.id) {
                alert('⚠️ Esta posición ya está ocupada');
                cell.style.backgroundColor = '';
                return;
            }
            
            // Enviar petición de mover
            const formData = new FormData();
            formData.append('table_id', draggedElement.id);
            formData.append('row', newRow);
            formData.append('col', newCol);
            
            const restaurantId = '<?= $restaurant['id'] ?>';
            fetch(`/grg/owner/restaurants/${restaurantId}/plano/move`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': '<?= \App\Services\CSRFProtection::generateToken() ?>' },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo mover'));
                }
                cell.style.backgroundColor = '';
            })
            .catch(err => {
                alert('Error de conexión');
                cell.style.backgroundColor = '';
            });
            
            draggedElement = null;
        });
    });
});
</script>

<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/app.php'; ?>
