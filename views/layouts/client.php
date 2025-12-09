<?php ob_start(); ?>

<div class="container">
    <h1 class="mb-4">Mi Panel</h1>
    
    <div class="row mb-4">
        <!-- Upcoming Reservations -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Próximas Reservas</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($upcomingReservations)): ?>
                        <div class="list-group">
                            <?php foreach ($upcomingReservations as $reservation): ?>
                                <a href="/grg/reservations/<?= $reservation['id'] ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($reservation['restaurant_name']) ?></h6>
                                        <small><?= date('d/m/Y H:i', strtotime($reservation['start_time'])) ?></small>
                                    </div>
                                    <p class="mb-1">
                                        <i class="bi bi-people"></i> <?= $reservation['guest_count'] ?> personas
                                        <i class="bi bi-table ms-2"></i> Mesa <?= htmlspecialchars($reservation['table_number']) ?>
                                    </p>
                                    <small class="text-muted"><?= htmlspecialchars($reservation['address']) ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No tienes reservas próximas.</p>
                        <a href="/grg/restaurants" class="btn btn-primary">Explorar Restaurantes</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="col-md-4">
            <div class="card shadow mb-3">
                <div class="card-body text-center">
                    <h3 class="display-4"><?= count($upcomingReservations) ?></h3>
                    <p class="text-muted mb-0">Reservas Próximas</p>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-body text-center">
                    <h3 class="display-4"><?= $unreadCount ?></h3>
                    <p class="text-muted mb-0">Notificaciones</p>
                    <?php if ($unreadCount > 0): ?>
                        <a href="/grg/notifications" class="btn btn-sm btn-outline-primary mt-2">Ver</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Past Reservations -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Reservas</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pastReservations)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Restaurante</th>
                                        <th>Fecha</th>
                                        <th>Personas</th>
                                        <th>Estado</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pastReservations as $reservation): 
                                        $statusClass = [
                                            'completed' => 'success',
                                            'cancelled' => 'secondary',
                                            'no_show' => 'danger',
                                            'rejected' => 'warning'
                                        ];
                                        $badgeClass = $statusClass[$reservation['status']] ?? 'secondary';
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['restaurant_name']) ?></td>
                                            <td><?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?></td>
                                            <td><?= $reservation['guest_count'] ?></td>
                                            <td><span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($reservation['status']) ?></span></td>
                                            <td>
                                                <a href="/grg/reservations/<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                    Ver
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay reservas en el historial.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/app.php'; ?>
