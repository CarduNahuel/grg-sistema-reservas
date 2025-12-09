<?php ob_start(); ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> Editar Menú: <?= htmlspecialchars($category['name']) ?></h1>
        <a href="/grg/admin/menus" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información de la Categoría</h5>
                </div>
                <div class="card-body">
                    <form action="/grg/admin/menus/<?= $category['id'] ?>/update" method="POST">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label"><strong>Nombre de la Categoría</strong></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label"><strong>Descripción</strong></label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                            <small class="text-muted">Descripción breve de la categoría de menú</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                   <?= $category['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                <strong>Activa</strong> - La categoría será visible para los clientes
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                            <a href="/grg/admin/menus" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-info"></i> Información</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td><?= $category['id'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Restaurante:</strong></td>
                            <td><?= htmlspecialchars($category['restaurant_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Productos:</strong></td>
                            <td><?= count($items) ?> items</td>
                        </tr>
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>
                                <span class="badge bg-<?= $category['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $category['is_active'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos de la Categoría -->
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-basket"></i> Productos en esta Categoría</h5>
        </div>
        <div class="card-body">
            <?php if (empty($items)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No hay productos en esta categoría aún.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Descripción</th>
                                <th class="text-center">Precio</th>
                                <th class="text-center">Stock</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($item['description'] ?? '', 0, 40)) ?><?= strlen($item['description'] ?? '') > 40 ? '...' : '' ?></td>
                                <td class="text-center">
                                    <strong>$<?= number_format($item['price'] ?? 0, 2) ?></strong>
                                </td>
                                <td class="text-center">
                                    <?php if (isset($item['available_quantity'])): ?>
                                        <span class="badge bg-<?= $item['available_quantity'] > 0 ? 'success' : 'danger' ?>">
                                            <?= $item['available_quantity'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= ($item['is_available'] ?? true) ? 'success' : 'secondary' ?>">
                                        <?= ($item['is_available'] ?? true) ? 'Disponible' : 'No disponible' ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="/grg/admin/menu-items/<?= $item['id'] ?>/edit" class="btn btn-sm btn-primary" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
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

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layouts/app.php'; 
?>
