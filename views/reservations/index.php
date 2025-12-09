<?php ob_start(); ?>

<div class="container">
    <h1 class="mb-4">Mis Reservas</h1>

    <?php if (!empty($reservations)): ?>
        <div class="row">
            <?php foreach ($reservations as $reservation): 
                $statusClass = [
                    'pending' => 'warning',
                    'confirmed' => 'success',
                    'rejected' => 'danger',
                    'cancelled' => 'secondary',
                    'completed' => 'info',
                    'no_show' => 'dark'
                ];
                $badgeClass = $statusClass[$reservation['status']] ?? 'secondary';
            ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= htmlspecialchars($reservation['restaurant_name']) ?></h5>
                                <span class="badge bg-<?= $badgeClass ?>">
                                    <?= ucfirst($reservation['status']) ?>
                                </span>
                            </div>
                            
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($reservation['address']) ?>
                            </p>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-6">
                                    <small class="text-muted">Fecha</small><br>
                                    <strong><?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Hora</small><br>
                                    <strong><?= date('H:i', strtotime($reservation['start_time'])) ?></strong>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Mesa</small><br>
                                    <strong><?= htmlspecialchars($reservation['table_number']) ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Personas</small><br>
                                    <strong><?= $reservation['guest_count'] ?></strong>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <a href="/grg/reservations/<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Ver Detalles
                                </a>
                                
                                <a href="/grg/restaurants/<?= $reservation['restaurant_id'] ?>/menu" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-book"></i> Ver Menú
                                </a>
                                
                                <?php if ($reservation['status'] === 'pending' || $reservation['status'] === 'confirmed'): ?>
                                    <?php if (strtotime($reservation['start_time']) > time()): ?>
                                        <form method="POST" action="/grg/reservations/<?= $reservation['id'] ?>/cancel" 
                                              class="d-inline" onsubmit="return confirm('¿Estás seguro de cancelar esta reserva?')">
                                            <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle"></i> Cancelar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No tienes reservas aún.
            <a href="/grg/restaurants" class="alert-link">Explora restaurantes</a> y haz tu primera reserva.
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
