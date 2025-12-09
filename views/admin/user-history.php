<?php 
$title = 'Historial de Usuario'; 
ob_start(); 
?>

<div class="mb-4">
    <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Usuarios
    </a>
</div>

<!-- Información del usuario -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="avatar-circle" style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 2rem;">
                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                </div>
            </div>
            <div class="col">
                <h3 class="mb-1"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                <p class="text-muted mb-2"><?= htmlspecialchars($user['email']) ?></p>
                <div>
                    <?php
                    $badgeColors = [
                        'SUPERADMIN' => 'danger',
                        'OWNER' => 'primary',
                        'CLIENTE' => 'success',
                        'STAFF' => 'warning'
                    ];
                    $roleModel = new \App\Models\Role();
                    $role = $roleModel->find($user['role_id']);
                    $badgeColor = $badgeColors[$role['name']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?= $badgeColor ?> me-2">
                        <?= htmlspecialchars($role['name']) ?>
                    </span>
                    <?php if ($user['is_active']): ?>
                        <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Activo
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger">
                            <i class="bi bi-x-circle"></i> Inactivo
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-auto">
                <div class="text-end">
                    <small class="text-muted d-block">Registrado</small>
                    <strong><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Reservas recientes -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check"></i> Reservas Recientes
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($reservations)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No hay reservas registradas</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($reservations as $reservation): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?= htmlspecialchars($reservation['restaurant_name']) ?></h6>
                                        <p class="mb-1">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar"></i>
                                                <?= date('d/m/Y', strtotime($reservation['reservation_date'])) ?>
                                                <i class="bi bi-clock ms-2"></i>
                                                <?= date('H:i', strtotime($reservation['start_time'])) ?>
                                            </small>
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-people"></i> 
                                            <?= $reservation['guest_count'] ?> personas
                                        </small>
                                    </div>
                                    <div>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'info',
                                            'checked_in' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            'no_show' => 'secondary'
                                        ];
                                        $statusNames = [
                                            'pending' => 'Pendiente',
                                            'confirmed' => 'Confirmada',
                                            'checked_in' => 'Check-in',
                                            'completed' => 'Completada',
                                            'cancelled' => 'Cancelada',
                                            'no_show' => 'No Show'
                                        ];
                                        $statusColor = $statusColors[$reservation['status']] ?? 'secondary';
                                        $statusName = $statusNames[$reservation['status']] ?? $reservation['status'];
                                        ?>
                                        <span class="badge bg-<?= $statusColor ?>">
                                            <?= $statusName ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Historial de auditoría -->
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history"></i> Historial de Auditoría
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($auditHistory)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-3">No hay registros de auditoría</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($auditHistory as $log): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?php
                                            $actionIcons = [
                                                'activated_user' => 'check-circle text-success',
                                                'deactivated_user' => 'x-circle text-danger',
                                                'changed_user_role' => 'shield text-warning',
                                                'admin_password_reset' => 'key text-info'
                                            ];
                                            $actionNames = [
                                                'activated_user' => 'Usuario Activado',
                                                'deactivated_user' => 'Usuario Desactivado',
                                                'changed_user_role' => 'Rol Cambiado',
                                                'admin_password_reset' => 'Reset de Contraseña'
                                            ];
                                            $icon = $actionIcons[$log['action']] ?? 'info-circle';
                                            $actionName = $actionNames[$log['action']] ?? $log['action'];
                                            ?>
                                            <i class="bi bi-<?= $icon ?>"></i>
                                            <?= htmlspecialchars($actionName) ?>
                                        </h6>
                                        <p class="mb-1">
                                            <small class="text-muted">
                                                <?= htmlspecialchars($log['action']) ?>
                                            </small>
                                        </p>
                                        <small class="text-muted">
                                            Por: <?= htmlspecialchars($log['admin_name'] ?? 'Sistema') ?>
                                            • <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/app.php'; 
?>
