<?php ob_start(); ?>

<div class="container">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-calendar-check"></i> Detalles de Reserva</h1>
                <a href="/grg/reservations" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>

            <!-- Reservation Status Alert -->
            <?php
            $statusBadge = match($reservation['status']) {
                'pending' => '<span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock"></i> Pendiente de Confirmación</span>',
                'confirmed' => '<span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> Confirmada</span>',
                'rejected' => '<span class="badge bg-danger fs-6"><i class="bi bi-x-circle"></i> Rechazada</span>',
                'cancelled' => '<span class="badge bg-secondary fs-6"><i class="bi bi-calendar-x"></i> Cancelada</span>',
                'completed' => '<span class="badge bg-primary fs-6"><i class="bi bi-check-all"></i> Completada</span>',
                'no_show' => '<span class="badge bg-dark fs-6"><i class="bi bi-person-x"></i> No se presentó</span>',
                default => '<span class="badge bg-secondary fs-6">' . ucfirst($reservation['status']) . '</span>'
            };
            ?>
            
            <div class="alert alert-<?= $reservation['status'] === 'confirmed' ? 'success' : ($reservation['status'] === 'pending' ? 'warning' : 'secondary') ?> d-flex justify-content-between align-items-center">
                <div>
                    <strong>Estado:</strong> <?= $statusBadge ?>
                </div>
                <div>
                    <small class="text-muted">ID: #<?= $reservation['id'] ?></small>
                </div>
            </div>

            <!-- Restaurant Information -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-shop"></i> Restaurante</h5>
                </div>
                <div class="card-body">
                    <h4><?= htmlspecialchars($restaurant['name']) ?></h4>
                    <p class="mb-2">
                        <i class="bi bi-geo-alt"></i> 
                        <?= htmlspecialchars($restaurant['address']) ?>, 
                        <?= htmlspecialchars($restaurant['city']) ?>
                    </p>
                    <p class="mb-0">
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($restaurant['phone']) ?>
                    </p>
                    <?php if ($restaurant['email']): ?>
                        <p class="mb-0">
                            <i class="bi bi-envelope"></i> <?= htmlspecialchars($restaurant['email']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reservation Details -->
            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Detalles de la Reserva</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-calendar3"></i> Fecha:</strong><br>
                            <span class="fs-5"><?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-clock"></i> Hora:</strong><br>
                            <span class="fs-5">
                                <?= date('H:i', strtotime($reservation['start_time'])) ?> - 
                                <?= date('H:i', strtotime($reservation['end_time'])) ?>
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-people"></i> Número de Personas:</strong><br>
                            <span class="fs-5"><?= $reservation['guest_count'] ?></span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong><i class="bi bi-table"></i> Mesa:</strong><br>
                            <span class="fs-5">
                                Mesa <?= htmlspecialchars($table['table_number']) ?>
                                <?php if ($table['area']): ?>
                                    (<?= htmlspecialchars($table['area']) ?>)
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($reservation['special_requests']): ?>
                        <hr>
                        <div class="alert alert-info mb-0">
                            <strong><i class="bi bi-chat-left-text"></i> Solicitudes Especiales:</strong><br>
                            <?= nl2br(htmlspecialchars($reservation['special_requests'])) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($reservation['notes']): ?>
                        <hr>
                        <div class="alert alert-secondary mb-0">
                            <strong><i class="bi bi-sticky"></i> Notas del Restaurante:</strong><br>
                            <?= nl2br(htmlspecialchars($reservation['notes'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <?php if ($reservation['status'] === 'pending' || $reservation['status'] === 'confirmed'): ?>
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-gear"></i> Acciones</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($reservation['user_id'] == $_SESSION['user_id'] && in_array($reservation['status'], ['pending', 'confirmed'])): ?>
                            <button class="btn btn-warning me-2" onclick="openModifyModal(<?= $reservation['id'] ?>)">
                                <i class="bi bi-pencil"></i> Modificar Reserva
                            </button>
                            <button class="btn btn-danger" onclick="cancelReservation(<?= $reservation['id'] ?>)">
                                <i class="bi bi-x-circle"></i> Cancelar Reserva
                            </button>
                        <?php endif; ?>

                        <?php
                        $authService = new \App\Services\AuthService();
                        if ($authService->canManageRestaurant($reservation['restaurant_id'])): 
                        ?>
                            <?php if ($reservation['status'] === 'pending'): ?>
                                <button class="btn btn-success me-2" onclick="confirmReservation(<?= $reservation['id'] ?>)">
                                    <i class="bi bi-check-circle"></i> Confirmar Reserva
                                </button>
                                <button class="btn btn-danger" onclick="rejectReservation(<?= $reservation['id'] ?>)">
                                    <i class="bi bi-x-circle"></i> Rechazar Reserva
                                </button>
                            <?php elseif ($reservation['status'] === 'confirmed'): ?>
                                <?php if ($reservation['check_in_time']): ?>
                                    <!-- Cliente ya hizo check-in -->
                                    <div class="alert alert-success d-inline-block me-2 mb-0">
                                        <i class="bi bi-person-check-fill"></i> 
                                        <strong>Cliente ingresó</strong> 
                                        <small class="ms-2"><?= date('H:i', strtotime($reservation['check_in_time'])) ?></small>
                                    </div>
                                    <button class="btn btn-info me-2" onclick="viewAccount(<?= $reservation['id'] ?>)">
                                        <i class="bi bi-receipt"></i> Ver Cuenta
                                    </button>
                                    <button class="btn btn-primary" onclick="completeReservation(<?= $reservation['id'] ?>)">
                                        <i class="bi bi-check-all"></i> Completar Reserva
                                    </button>
                                <?php else: ?>
                                    <!-- Sin check-in aún -->
                                    <button class="btn btn-warning me-2" onclick="checkInReservation(<?= $reservation['id'] ?>)">
                                        <i class="bi bi-person-check"></i> Marcar Check-in
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="completeReservation(<?= $reservation['id'] ?>)">
                                        <i class="bi bi-check-all"></i> Completar Reserva
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <strong>Creada:</strong> 
                            <?= date('d/m/Y H:i', strtotime($reservation['created_at'])) ?>
                        </div>
                        <?php if ($reservation['confirmed_at']): ?>
                            <div class="timeline-item">
                                <strong>Confirmada:</strong> 
                                <?= date('d/m/Y H:i', strtotime($reservation['confirmed_at'])) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($reservation['check_in_time']): ?>
                            <div class="timeline-item">
                                <strong>Check-in:</strong> 
                                <?= date('d/m/Y H:i', strtotime($reservation['check_in_time'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Menu Button -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-book"></i> Menú</h5>
                </div>
                <div class="card-body">
                    <a href="/grg/restaurants/<?= $restaurant['id'] ?>/menu" class="btn btn-warning w-100">
                        <i class="bi bi-book"></i> Ver Menú del Restaurante
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modificar Reserva -->
<div class="modal fade" id="modifyReservationModal" tabindex="-1" aria-labelledby="modifyReservationLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modifyReservationLabel">Modificar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="modifyReservationForm">
                    <div class="mb-3">
                        <label for="modifyDate" class="form-label">Fecha</label>
                        <input type="date" class="form-control" id="modifyDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="modifyStartTime" class="form-label">Hora de Inicio</label>
                        <input type="time" class="form-control" id="modifyStartTime" required>
                    </div>
                    <div class="mb-3">
                        <label for="modifyGuestCount" class="form-label">Cantidad de Comensales</label>
                        <input type="number" class="form-control" id="modifyGuestCount" min="1" required>
                    </div>
                    <div id="availabilityMessage" class="alert d-none" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="submitModifyReservation()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?php $scripts = ob_start(); ?>
<script>
function openModifyModal(id) {
    // Cargar datos actuales en el modal
    document.getElementById('modifyDate').value = '<?= date('Y-m-d', strtotime($reservation['reservation_date'])) ?>';
    document.getElementById('modifyStartTime').value = '<?= date('H:i', strtotime($reservation['start_time'])) ?>';
    document.getElementById('modifyGuestCount').value = <?= $reservation['guest_count'] ?>;
    document.getElementById('modifyReservationForm').dataset.reservationId = id;
    
    const modal = new bootstrap.Modal(document.getElementById('modifyReservationModal'));
    modal.show();
}

function submitModifyReservation() {
    const reservationId = document.getElementById('modifyReservationForm').dataset.reservationId;
    const date = document.getElementById('modifyDate').value;
    const startTime = document.getElementById('modifyStartTime').value;
    const guestCount = document.getElementById('modifyGuestCount').value;
    
    fetchWithCsrf('/grg/reservations/' + reservationId + '/modify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            reservation_date: date,
            start_time: startTime,
            guest_count: guestCount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Reserva modificada correctamente');
            location.reload();
        } else {
            document.getElementById('availabilityMessage').classList.remove('d-none');
            document.getElementById('availabilityMessage').className = 'alert alert-danger d-block';
            document.getElementById('availabilityMessage').textContent = data.message || 'Error al modificar la reserva';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}

function cancelReservation(id) {
    if (!confirm('¿Estás seguro de que deseas cancelar esta reserva?')) return;
    
    fetchWithCsrf('/grg/reservations/' + id + '/cancel', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al cancelar la reserva');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cancelar la reserva');
    });
}

function confirmReservation(id) {
    if (!confirm('¿Confirmar esta reserva?')) return;
    
    fetchWithCsrf('/grg/reservations/' + id + '/confirm', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al confirmar la reserva');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al confirmar la reserva');
    });
}

function rejectReservation(id) {
    if (!confirm('¿Rechazar esta reserva?')) return;
    
    fetchWithCsrf('/grg/reservations/' + id + '/reject', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al rechazar la reserva');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al rechazar la reserva');
    });
}

function checkInReservation(id) {
    if (!confirm('¿Marcar check-in para esta reserva?')) return;
    
    fetchWithCsrf('/grg/reservations/' + id + '/checkin', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al marcar check-in');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al marcar check-in');
    });
}

function completeReservation(id) {
    if (!confirm('¿Marcar esta reserva como completada?')) return;
    
    fetchWithCsrf('/grg/reservations/' + id + '/complete', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al completar la reserva');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al completar la reserva');
    });
}

function viewAccount(id) {
    // Por ahora muestra un mensaje, puedes expandir esto para mostrar un modal con consumos
    alert('Funcionalidad de cuenta en desarrollo.\n\nAquí podrás:\n- Ver consumos de la mesa\n- Agregar ítems\n- Generar factura\n- Procesar pago');
    
    // Opción alternativa: redirigir a una página de cuenta
    // window.location.href = '/grg/reservations/' + id + '/account';
}
</script>
<?php 
$scripts = ob_get_clean();
$content = ob_get_clean(); 
include __DIR__ . '/../layouts/app.php'; 
?>
