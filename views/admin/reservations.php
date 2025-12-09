<?php 
$title = 'Gestión de Reservas'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-calendar-check"></i> Gestión de Reservas</h2>
</div>

<!-- Filtros y búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/reservations') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Email, restaurante o ID">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="status">
                    <option value="">Todos los estados</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="confirmed" <?= $statusFilter === 'confirmed' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="rejected" <?= $statusFilter === 'rejected' ? 'selected' : '' ?>>Rechazada</option>
                    <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Completada</option>
                    <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                    <option value="no_show" <?= $statusFilter === 'no_show' ? 'selected' : '' ?>>No Show</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Restaurante</label>
                <select class="form-select" name="restaurant">
                    <option value="">Todos los restaurantes</option>
                    <?php foreach ($restaurants as $restaurant): ?>
                        <option value="<?= $restaurant['id'] ?>" <?= $restaurantFilter == $restaurant['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($restaurant['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de reservas -->
<div class="card">
    <div class="card-body">
        <?php if (empty($reservations)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No se encontraron reservas</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Restaurante</th>
                            <th>Fecha y Hora</th>
                            <th>Personas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">#<?= $reservation['id'] ?></span>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?= htmlspecialchars($reservation['user_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($reservation['user_email']) ?></small>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($reservation['restaurant_name']) ?></td>
                                <td>
                                    <div>
                                        <div><?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($reservation['start_time'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $reservation['guest_count'] ?></span>
                                </td>
                                <td>
                                    <?php
                                    $statusBadges = [
                                        'pending' => 'warning',
                                        'confirmed' => 'info',
                                        'rejected' => 'danger',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        'no_show' => 'secondary'
                                    ];
                                    $statusNames = [
                                        'pending' => 'Pendiente',
                                        'confirmed' => 'Confirmada',
                                        'rejected' => 'Rechazada',
                                        'completed' => 'Completada',
                                        'cancelled' => 'Cancelada',
                                        'no_show' => 'No Show'
                                    ];
                                    $statusColor = $statusBadges[$reservation['status']] ?? 'secondary';
                                    $statusName = $statusNames[$reservation['status']] ?? $reservation['status'];
                                    ?>
                                    <span class="badge bg-<?= $statusColor ?>">
                                        <?= $statusName ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Cambiar estado -->
                                        <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#statusModal<?= $reservation['id'] ?>"
                                            title="Cambiar estado">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Notificar -->
                                        <form method="POST" 
                                              action="<?= url('/admin/reservations/notify') ?>" 
                                              style="display: inline;"
                                              onsubmit="return confirm('¿Enviar notificación al usuario?')">
                                            <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                            <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="Notificar usuario">
                                                <i class="bi bi-bell"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i>
                    Total de reservas: <strong><?= count($reservations) ?></strong>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modales para cambiar estado (fuera de la tabla) -->
<?php if (!empty($reservations)): ?>
    <?php foreach ($reservations as $reservation): ?>
        <div class="modal fade" id="statusModal<?= $reservation['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar Estado de Reserva</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?= url('/admin/reservations/status') ?>" id="statusForm<?= $reservation['id'] ?>">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                        <input type="hidden" name="table_ids" id="selectedTables<?= $reservation['id'] ?>" value="">
                        <div class="modal-body">
                            <p><strong>Reserva #<?= $reservation['id'] ?></strong></p>
                            <p>
                                <strong><?= htmlspecialchars($reservation['restaurant_name']) ?></strong><br>
                                <span style="font-size: 1.1em; color: #d97706; font-weight: 600;">
                                    <i class="bi bi-calendar-event"></i> <?= date('d/m/Y', strtotime($reservation['start_time'])) ?> 
                                    <i class="bi bi-clock"></i> <?= date('H:i', strtotime($reservation['start_time'])) ?>
                                </span>
                            </p>
                            <p class="text-muted">
                                <i class="bi bi-people"></i> <?= $reservation['guest_count'] ?> personas
                                <?php if ($reservation['preferred_zone']): ?>
                                    | <i class="bi bi-geo-alt"></i> Zona preferida: <?= htmlspecialchars($reservation['preferred_zone']) ?>
                                <?php endif; ?>
                            </p>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">Nuevo Estado</label>
                                <select class="form-select" name="status" id="statusSelect<?= $reservation['id'] ?>" required onchange="togglePlano<?= $reservation['id'] ?>(this.value)">
                                    <option value="">Seleccionar estado</option>
                                    <option value="pending" <?= $reservation['status'] === 'pending' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="confirmed" <?= $reservation['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmada</option>
                                    <option value="rejected" <?= $reservation['status'] === 'rejected' ? 'selected' : '' ?>>Rechazada</option>
                                    <option value="completed" <?= $reservation['status'] === 'completed' ? 'selected' : '' ?>>Completada</option>
                                    <option value="cancelled" <?= $reservation['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelada</option>
                                    <option value="no_show" <?= $reservation['status'] === 'no_show' ? 'selected' : '' ?>>No Show</option>
                                </select>
                            </div>
                            
                            <!-- Plano para asignar mesa -->
                            <div id="planoContainer<?= $reservation['id'] ?>" style="display: none;">
                                <hr>
                                <h6 style="color: #d97706; font-weight: 600;">
                                    <i class="bi bi-table"></i> Plano del <?= date('d/m/Y', strtotime($reservation['start_time'])) ?>
                                </h6>
                                <p class="text-muted small">Haz clic en una mesa disponible para asignarla a esta reserva</p>
                                <div id="planoInfo<?= $reservation['id'] ?>" class="alert alert-info small mb-2" style="display: none;">
                                    Mesa seleccionada: <strong id="planoInfoText<?= $reservation['id'] ?>"></strong>
                                </div>
                                <div style="max-height: 400px; overflow-y: auto;">
                                    <?php
                                    // Usar la partial para renderizar el plano
                                    $tables = $reservation['plano_elements'] ?? [];
                                    $gridId = 'adminReservation' . $reservation['id'];
                                    $onCellClick = 'toggleMesa' . $reservation['id'] . '(TABLEID)';
                                    $selectable = true;
                                    $assignedIds = array_map(function($t) { return $t['id']; }, $reservation['assigned_tables'] ?? []);
                                    $occupiedIds = array_map(function($t) { return $t['id']; }, $reservation['occupied_tables'] ?? []);
                                    include __DIR__ . '/../partials/plano_grid.php';
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <script>
        // Pre-cargar mesas asignadas desde PHP
        const selectedTables<?= $reservation['id'] ?> = <?= json_encode(
            array_map(function($t) {
                return ['id' => (int)$t['id'], 'label' => 'Mesa ' . $t['table_number']];
            }, $reservation['assigned_tables'] ?? [])
        ) ?>;

        // Inicializar el input con las mesas asignadas
        document.addEventListener('DOMContentLoaded', () => {
            if (selectedTables<?= $reservation['id'] ?>.length > 0) {
                document.getElementById('selectedTables<?= $reservation['id'] ?>').value = 
                    selectedTables<?= $reservation['id'] ?>.map(t => t.id).join(',');
                renderSelectionInfo<?= $reservation['id'] ?>();
            }
        });

        // Función para toggle mesa cuando se hace clic
        function toggleMesa<?= $reservation['id'] ?>(tableId) {
            console.log('Click en mesa:', tableId);
            const modal = document.getElementById('statusModal<?= $reservation['id'] ?>');
            const info = modal ? modal.querySelector('#planoInfo<?= $reservation['id'] ?>') : null;
            const infoText = modal ? modal.querySelector('#planoInfoText<?= $reservation['id'] ?>') : null;

            // Selección única: limpiar y dejar solo la mesa clickeada
            selectedTables<?= $reservation['id'] ?>.length = 0;
            selectedTables<?= $reservation['id'] ?>.push({
                id: tableId,
                label: 'Mesa ' + tableId
            });
            console.log('Mesa asignada');
            
            // Guardar en input
            const hiddenInput = document.getElementById('selectedTables<?= $reservation['id'] ?>');
            if (hiddenInput) {
                hiddenInput.value = selectedTables<?= $reservation['id'] ?>
                    .map(t => t.id)
                    .join(',');
            }
            
            // Actualizar colores
            updateGridColors<?= $reservation['id'] ?>();
            renderSelectionInfo<?= $reservation['id'] ?>(info, infoText);
            
            // Forzar estado "Confirmada"
            const statusSelect = document.getElementById('statusSelect<?= $reservation['id'] ?>');
            if (statusSelect) {
                statusSelect.value = 'confirmed';
            }
            togglePlano<?= $reservation['id'] ?>('confirmed');
        }

        function updateGridColors<?= $reservation['id'] ?>() {
            const selectedIds = new Set(selectedTables<?= $reservation['id'] ?>.map(t => t.id));
            
            // Buscar todas las celdas del plano y actualizar colores
            document.querySelectorAll('.plano-cell-adminReservation<?= $reservation['id'] ?>').forEach(cell => {
                const cellId = parseInt(cell.dataset.id);
                if (cellId && selectedIds.has(cellId)) {
                    cell.classList.add('mesa-seleccionada');
                } else {
                    cell.classList.remove('mesa-seleccionada');
                }
            });
        }

        function renderSelectionInfo<?= $reservation['id'] ?>(infoEl = null, textEl = null) {
            const modal = document.getElementById('statusModal<?= $reservation['id'] ?>');
            const info = infoEl || (modal ? modal.querySelector('#planoInfo<?= $reservation['id'] ?>') : null);
            const text = textEl || (modal ? modal.querySelector('#planoInfoText<?= $reservation['id'] ?>') : null);
            
            if (!info || !text) {
                return;
            }
            
            if (!selectedTables<?= $reservation['id'] ?>.length) {
                info.style.display = 'none';
                return;
            }
            
            const labels = selectedTables<?= $reservation['id'] ?>.map(t => t.label).join(', ');
            info.style.display = 'block';
            text.textContent = `Mesas: ${labels} | Comensales: <?= (int)$reservation['guest_count'] ?>`;
        }

        function togglePlano<?= $reservation['id'] ?>(status) {
            const container = document.getElementById('planoContainer<?= $reservation['id'] ?>');
            if (status === 'confirmed') {
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
                selectedTables<?= $reservation['id'] ?>.length = 0;
                const hiddenInput = document.getElementById('selectedTables<?= $reservation['id'] ?>');
                if (hiddenInput) {
                    hiddenInput.value = '';
                }
                renderSelectionInfo<?= $reservation['id'] ?>();
            }
        }

        // Al cargar la página, mostrar plano si ya está confirmada
        document.addEventListener('DOMContentLoaded', () => {
            const currentStatus = '<?= $reservation['status'] ?>';
            const select = document.getElementById('statusSelect<?= $reservation['id'] ?>');
            if (select) {
                select.value = currentStatus || '';
                if (currentStatus === 'confirmed') {
                    document.getElementById('planoContainer<?= $reservation['id'] ?>').style.display = 'block';
                }
            }
        });

        // Validar antes de enviar formulario
        document.getElementById('statusForm<?= $reservation['id'] ?>').addEventListener('submit', function(e) {
            const status = document.getElementById('statusSelect<?= $reservation['id'] ?>').value;
            const tableIds = document.getElementById('selectedTables<?= $reservation['id'] ?>').value.trim();
            
            if (status === 'confirmed' && !tableIds) {
                const proceed = confirm('⚠️ Vas a confirmar sin asignar mesas desde el plano.\n\nHaz clic en el plano para asignar mesas antes de guardar.\n\n¿Deseas continuar sin mesas?');
                if (!proceed) {
                    e.preventDefault();
                    return false;
                }
            }
            return true;
        });
        </script>
    <?php endforeach; ?>
<?php endif; ?>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/app.php'; 
?>
