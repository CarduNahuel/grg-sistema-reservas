<?php ob_start(); ?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-bell"></i> Notificaciones</h1>
                <?php if (!empty($notifications)): ?>
                    <form method="POST" action="/grg/notifications/read-all">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-check-all"></i> Marcar todas como leídas
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            
            <div class="card shadow">
                <div class="card-body">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                            <h4 class="text-muted mt-3">No tienes notificaciones</h4>
                            <p class="text-muted">Cuando recibas notificaciones, aparecerán aquí.</p>
                            <a href="/grg/dashboard" class="btn btn-primary mt-3">
                                <i class="bi bi-house"></i> Volver al Dashboard
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item <?= !$notification['is_read'] ? 'bg-light' : '' ?>">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-2">
                                                <?php
                                                $iconClass = match($notification['type']) {
                                                    'reservation_created' => 'bi-calendar-plus text-primary',
                                                    'reservation_confirmed' => 'bi-check-circle text-success',
                                                    'reservation_rejected' => 'bi-x-circle text-danger',
                                                    'reservation_cancelled' => 'bi-calendar-x text-warning',
                                                    'reservation_reminder' => 'bi-clock text-info',
                                                    'payment_required' => 'bi-credit-card text-warning',
                                                    default => 'bi-info-circle text-secondary'
                                                };
                                                ?>
                                                <i class="bi <?= $iconClass ?> fs-4 me-2"></i>
                                                <h6 class="mb-0"><?= htmlspecialchars($notification['title']) ?></h6>
                                                <?php if (!$notification['is_read']): ?>
                                                    <span class="badge bg-primary ms-2">Nuevo</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-1 ms-5"><?= htmlspecialchars($notification['message']) ?></p>
                                            <div class="ms-5">
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i> 
                                                    <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                                </small>
                                                <?php if ($notification['email_sent']): ?>
                                                    <small class="text-success ms-3">
                                                        <i class="bi bi-envelope-check"></i> Email enviado
                                                    </small>
                                                <?php endif; ?>
                                                <?php if ($notification['reservation_id']): ?>
                                                    <a href="/grg/reservations/<?= $notification['reservation_id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary ms-3">
                                                        <i class="bi bi-eye"></i> Ver reserva
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    onclick="markAsRead(<?= $notification['id'] ?>)">
                                                <i class="bi bi-check"></i> Marcar leída
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $scripts = ob_start(); ?>
<script>
function markAsRead(notificationId) {
    fetchWithCsrf('/grg/notifications/' + notificationId + '/read', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error al marcar como leída');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al marcar como leída');
    });
}
</script>
<?php 
$scripts = ob_get_clean();
$content = ob_get_clean(); 
include __DIR__ . '/../layouts/app.php'; 
?>
