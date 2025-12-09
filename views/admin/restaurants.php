<?php 
$title = 'Gestión de Restaurantes'; 
ob_start(); 
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-shop"></i> Gestión de Restaurantes</h2>
</div>

<!-- Filtros y búsqueda -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/restaurants') ?>" class="row g-3">
            <div class="col-md-6">
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
                <label class="form-label">Estado</label>
                <select class="form-select" name="status">
                    <option value="">Todos</option>
                    <option value="1" <?= $statusFilter === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="0" <?= $statusFilter === '0' ? 'selected' : '' ?>>Inactivos</option>
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

<!-- Tabla de restaurantes -->
<div class="card">
    <div class="card-body">
        <?php if (empty($restaurants)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">No se encontraron restaurantes</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Restaurante</th>
                            <th>Email</th>
                            <th>Propietario</th>
                            <th>Mesas</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if (!empty($restaurant['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($restaurant['image_url']) ?>" 
                                                 alt="<?= htmlspecialchars($restaurant['name']) ?>" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; margin-right: 10px;">
                                                <i class="bi bi-shop"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($restaurant['name']) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars($restaurant['address'] ?? 'N/A') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($restaurant['email']) ?></td>
                                <td>
                                    <small><?= htmlspecialchars($restaurant['owner_name'] ?? 'N/A') ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $restaurant['table_count'] ?> mesas</span>
                                </td>
                                <td>
                                    <?php if ($restaurant['is_active']): ?>
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
                                        <?= date('d/m/Y', strtotime($restaurant['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <!-- Gestionar Mesas -->
                                        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables') ?>" 
                                           class="btn btn-outline-secondary" 
                                           title="Gestionar Mesas">
                                            <i class="bi bi-table"></i>
                                        </a>

                                        <!-- Gestionar Menús -->
                                        <a href="/grg/admin/restaurants/<?= $restaurant['id'] ?>/menus" 
                                           class="btn btn-outline-warning" 
                                           title="Gestionar Menús">
                                            <i class="bi bi-book"></i>
                                        </a>
                                        
                                        <!-- Editar -->
                                        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/edit') ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Ver detalles -->
                                        <a href="<?= url('/restaurants/' . $restaurant['id']) ?>" 
                                           class="btn btn-outline-info" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <!-- Activar/Desactivar -->
                                        <form method="POST" 
                                              action="<?= url('/admin/restaurants/toggle-active') ?>" 
                                              style="display: inline;"
                                              onsubmit="return confirm('¿Cambiar estado de este restaurante?')">
                                            <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                            <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $restaurant['is_active'] ?>">
                                            <button type="submit" 
                                                    class="btn btn-outline-<?= $restaurant['is_active'] ? 'danger' : 'success' ?>"
                                                    title="<?= $restaurant['is_active'] ? 'Desactivar' : 'Activar' ?>">
                                                <i class="bi bi-<?= $restaurant['is_active'] ? 'x-circle' : 'check-circle' ?>"></i>
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
                    Total de restaurantes: <strong><?= count($restaurants) ?></strong>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/app.php'; 
?>
