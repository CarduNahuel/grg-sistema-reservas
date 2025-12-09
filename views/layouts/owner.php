<?php ob_start(); ?>

<div class="container">
    <h1 class="mb-4">Panel de Gestión</h1>
    
    <div class="row mb-4">
        <!-- Stats Cards -->
        <div class="col-md-3">
            <div class="card shadow stat-card">
                <div class="card-body">
                    <h6 class="text-muted">Total Reservas</h6>
                    <h2><?= $stats['total_reservations'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow stat-card warning">
                <div class="card-body">
                    <h6 class="text-muted">Pendientes</h6>
                    <h2><?= $stats['pending_reservations'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow stat-card success">
                <div class="card-body">
                    <h6 class="text-muted">Hoy</h6>
                    <h2><?= $stats['today_reservations'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow stat-card">
                <div class="card-body">
                    <h6 class="text-muted">Mesas</h6>
                    <h2><?= $stats['total_tables'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Restaurants -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-shop"></i> Mis Restaurantes</h5>
                    <a href="/grg/owner/restaurants/create" class="btn btn-sm btn-light">
                        <i class="bi bi-plus-circle"></i> Nuevo Restaurante
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($restaurants)): ?>
                        <div class="row">
                            <?php foreach ($restaurants as $restaurant): ?>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5><?= htmlspecialchars($restaurant['name']) ?></h5>
                                            <p class="text-muted mb-2">
                                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restaurant['city']) ?>
                                            </p>
                                            <?php if ($restaurant['requires_payment'] && $restaurant['payment_status'] !== 'paid'): ?>
                                                <span class="badge bg-warning text-dark">Pago Pendiente</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php endif; ?>
                                            <div class="mt-2">
                                                <a href="/grg/owner/restaurants/<?= $restaurant['id'] ?>" class="btn btn-sm btn-primary">
                                                    Gestionar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No tienes restaurantes registrados aún.</p>
                        <a href="/grg/owner/restaurants/create" class="btn btn-primary">Crear Mi Primer Restaurante</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Reservations -->
    <?php if (!empty($pendingReservations)): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Reservas Pendientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Personas</th>
                                        <th>Mesa</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReservations as $reservation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['first_name'] . ' ' . $reservation['last_name']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?></td>
                                            <td><?= date('H:i', strtotime($reservation['start_time'])) ?></td>
                                            <td><?= $reservation['guest_count'] ?></td>
                                            <td><?= htmlspecialchars($reservation['table_number']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="confirmReservation(<?= $reservation['id'] ?>)">
                                                    Confirmar
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="rejectReservation(<?= $reservation['id'] ?>)">
                                                    Rechazar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php ob_start(); ?>
<script>
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
            alert('Error: ' + data.message);
        }
    });
}

function rejectReservation(id) {
    const reason = prompt('Motivo del rechazo (opcional):');
    if (reason === null) return;
    
    fetchWithCsrf('/grg/reservations/' + id + '/reject', {
        method: 'POST',
        body: 'reason=' + encodeURIComponent(reason)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>
<?php $scripts = ob_get_clean(); ?>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/app.php'; ?>
