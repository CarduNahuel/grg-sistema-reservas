<?php ob_start(); ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-book"></i> Menús de <?= htmlspecialchars($restaurant['name']) ?></h1>
        <a href="/grg/admin/menus" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver a Menús
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-shop"></i> <?= htmlspecialchars($restaurant['name']) ?></h5>
                    <p class="text-muted mb-2">
                        <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restaurant['address'] ?? 'No especificada') ?>
                    </p>
                    <p class="text-muted">
                        <i class="bi bi-telephone"></i> <?= htmlspecialchars($restaurant['phone'] ?? 'No especificado') ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="bi bi-book fs-1 text-primary"></i>
                    <h4 class="mt-2"><?= count($categories) ?></h4>
                    <p class="text-muted mb-0">Categorías de Menú</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-list"></i> Categorías de Menú</h5>
        </div>
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Este restaurante aún no tiene categorías de menú configuradas.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th class="text-center">Productos</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                <td><?= htmlspecialchars(substr($category['description'] ?? '', 0, 50)) ?><?= strlen($category['description'] ?? '') > 50 ? '...' : '' ?></td>
                                <td class="text-center">
                                    <span class="badge bg-info"><?= $category['items_count'] ?> items</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-<?= $category['is_active'] ? 'success' : 'secondary' ?>" 
                                            onclick="toggleMenuStatus(<?= $category['id'] ?>)">
                                        <?= $category['is_active'] ? 'Activo' : 'Inactivo' ?>
                                    </button>
                                </td>
                                <td class="text-center">
                                    <a href="/grg/admin/menus/<?= $category['id'] ?>/edit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                    <?php if ($category['items_count'] == 0): ?>
                                        <button class="btn btn-sm btn-danger" onclick="deleteMenu(<?= $category['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
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

<?php $scripts = ob_start(); ?>
<script>
function toggleMenuStatus(menuId) {
    if (!confirm('¿Cambiar el estado de esta categoría?')) return;
    
    fetchWithCsrf('/grg/admin/menus/toggle-active', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'menu_id=' + menuId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al cambiar estado');
        }
    });
}

function deleteMenu(menuId) {
    if (!confirm('¿Estás seguro de eliminar esta categoría? Esta acción no se puede deshacer.')) return;
    
    fetchWithCsrf('/grg/admin/menus/' + menuId + '/delete', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error al eliminar');
        }
    });
}
</script>
<?php 
$scripts = ob_get_clean();
$content = ob_get_clean(); 
include __DIR__ . '/../layouts/app.php'; 
?>
