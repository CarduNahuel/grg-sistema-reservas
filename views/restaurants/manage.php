<?php ob_start(); ?>

<div class="container-fluid my-5">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-shop"></i> <?= htmlspecialchars($restaurant['name']) ?></h1>
            <p class="text-muted"><?= htmlspecialchars($restaurant['description']) ?></p>
        </div>
        <div class="col-auto">
            <a href="/grg/owner/restaurants/<?= $restaurant['id'] ?>/edit" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Editar
            </a>
            <a href="/grg/owner/restaurants/<?= $restaurant['id'] ?>/menu" class="btn btn-success">
                <i class="bi bi-book"></i> Menú
            </a>
            <a href="/grg/owner/restaurants/<?= $restaurant['id'] ?>/plano" class="btn btn-info">
                <i class="bi bi-grid-3x3"></i> Plano
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Reservas Hoy</h6>
                    <h2 class="text-primary"><?= count($todayReservations) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Reservas Pendientes</h6>
                    <h2 class="text-warning"><?= count($pendingReservations) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Mesas Totales</h6>
                    <h2 class="text-info"><?= count($tables) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title text-muted">Estado</h6>
                    <h2>
                        <span class="badge bg-<?= $restaurant['is_active'] ? 'success' : 'danger' ?>">
                            <?= $restaurant['is_active'] ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Reservas Pendientes</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingReservations)): ?>
                        <p class="text-muted">No hay reservas pendientes.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Personas</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReservations as $reservation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['customer_name'] ?? 'N/A') ?></td>
                                            <td><?= date('d/m H:i', strtotime($reservation['reservation_date'])) ?></td>
                                            <td><?= $reservation['guests'] ?? $reservation['guest_count'] ?? 'N/A' ?></td>
                                            <td>
                                                <a href="/grg/admin/reservations" class="btn btn-sm btn-primary">Ver</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Reservas de Hoy</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($todayReservations)): ?>
                        <p class="text-muted">No hay reservas para hoy.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Personas</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayReservations as $reservation): ?>
                                        <tr>
                                            <td><?= date('H:i', strtotime($reservation['reservation_date'])) ?></td>
                                            <td><?= htmlspecialchars($reservation['customer_name'] ?? 'N/A') ?></td>
                                            <td><?= $reservation['guests'] ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $reservation['status'] === 'confirmed' ? 'success' : 
                                                    ($reservation['status'] === 'pending' ? 'warning' : 'danger')
                                                ?>">
                                                    <?= ucfirst($reservation['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Restaurante</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Dirección:</strong> <?= htmlspecialchars($restaurant['address']) ?></p>
                            <p><strong>Ciudad:</strong> <?= htmlspecialchars($restaurant['city']) ?>
                                <?php if ($restaurant['state']): ?>, <?= htmlspecialchars($restaurant['state']) ?><?php endif; ?>
                            </p>
                            <p><strong>Teléfono:</strong> <?= htmlspecialchars($restaurant['phone']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Email:</strong> <?= htmlspecialchars($restaurant['email']) ?></p>
                            <p><strong>Horarios:</strong> 
                                <?= date('H:i', strtotime($restaurant['opening_time'])) ?> - 
                                <?= date('H:i', strtotime($restaurant['closing_time'])) ?>
                            </p>
                            <p><strong>Mesas Totales:</strong> <?= count($tables) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
