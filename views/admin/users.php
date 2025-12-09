<?php 
$title = 'Gestión de Usuarios'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-people-fill"></i> Gestión de Usuarios</h2>
</div>

<!-- Filtros y búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/users') ?>" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" 
                           class="form-control" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Nombre o email">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Rol</label>
                <select class="form-select" name="role">
                    <option value="">Todos los roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= $roleFilter == $role['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Inactivos</option>
                </select>
            </div>
            <div class="col-md-2">
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

<!-- Tabla de usuarios -->
<div class="card">
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No se encontraron usuarios</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th width="250">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            <?= strtoupper(substr($user['name'], 0, 2)) ?>
                                        </div>
                                        <div>{{ }}
                                            <div class="fw-bold"><?= htmlspecialchars($user['name']) ?></div>
                                            <?php if ($user['id'] == $currentUser['id']): ?>
                                                <span class="badge bg-info">Tú</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php
                                    $badgeColors = [
                                        'SUPERADMIN' => 'danger',
                                        'OWNER' => 'primary',
                                        'CLIENTE' => 'success',
                                        'STAFF' => 'warning'
                                    ];
                                    $badgeColor = $badgeColors[$user['role_name']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badgeColor ?>">
                                        <?= htmlspecialchars($user['role_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <!-- Ver historial -->
                                        <a href="<?= url('/admin/users/' . $user['id'] . '/history') ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Ver historial">
                                            <i class="bi bi-clock-history"></i>
                                        </a>

                                        <!-- Cambiar rol -->
                                        <?php if ($user['id'] != $currentUser['id']): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#roleModal<?= $user['id'] ?>"
                                                    title="Cambiar rol">
                                                <i class="bi bi-shield"></i>
                                            </button>

                                            <!-- Activar/Desactivar -->
                                            <form method="POST" 
                                                  action="<?= url('/admin/users/toggle-active') ?>" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('¿Estás seguro de cambiar el estado de este usuario?')">
                                                <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="current_status" value="<?= $user['is_active'] ?>">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-<?= $user['is_active'] ? 'danger' : 'success' ?>"
                                                        title="<?= $user['is_active'] ? 'Desactivar' : 'Activar' ?>">
                                                    <i class="bi bi-<?= $user['is_active'] ? 'x-circle' : 'check-circle' ?>"></i>
                                                </button>
                                            </form>

                                            <!-- Resetear contraseña -->
                                            <form method="POST" 
                                                  action="<?= url('/admin/users/reset-password') ?>" 
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Se enviará un email al usuario con un enlace para restablecer su contraseña. ¿Continuar?')">
                                                <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" 
                                                        class="btn btn-sm btn-secondary"
                                                        title="Resetear contraseña">
                                                    <i class="bi bi-key"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled title="No puedes modificar tu propia cuenta">
                                                <i class="bi bi-lock"></i>
                                            </button>
                                        <?php endif; ?>
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
                    Total de usuarios: <strong><?= count($users) ?></strong>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($users)): ?>
    <?php foreach ($users as $user): ?>
        <?php if ($user['id'] == $currentUser['id']) continue; ?>
        <?php
            $badgeColors = [
                'SUPERADMIN' => 'danger',
                'OWNER' => 'primary',
                'CLIENTE' => 'success',
                'STAFF' => 'warning'
            ];
            $badgeColor = $badgeColors[$user['role_name']] ?? 'secondary';
        ?>
        <div class="modal fade" id="roleModal<?= $user['id'] ?>" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cambiar Rol de Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="<?= url('/admin/users/change-role') ?>">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <div class="modal-body">
                            <p><strong>Usuario:</strong> <?= htmlspecialchars($user['name']) ?></p>
                            <p><strong>Rol actual:</strong> 
                                <span class="badge bg-<?= $badgeColor ?>">
                                    <?= htmlspecialchars($user['role_name']) ?>
                                </span>
                            </p>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label">Nuevo Rol</label>
                                <select class="form-select" name="role_id" required>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?= $role['id'] ?>" 
                                                <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($role['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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
    <?php endforeach; ?>
<?php endif; ?>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/app.php'; 
?>
