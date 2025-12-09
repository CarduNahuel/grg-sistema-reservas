<?php ob_start(); ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="bi bi-pencil"></i> Editar Producto: <?= htmlspecialchars($item['name']) ?></h1>
        <a href="/grg/admin/menus/<?= $item['category_id'] ?>/edit" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Producto</h5>
                </div>
                <div class="card-body">
                    <form action="/grg/admin/menu-items/<?= $item['id'] ?>/update" method="POST">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        
                        <div class="mb-3">
                            <label for="name" class="form-label"><strong>Nombre del Producto</strong></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label"><strong>Descripción</strong></label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                            <small class="text-muted">Descripción detallada del producto</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label"><strong>Precio ($)</strong></label>
                                    <input type="number" class="form-control" id="price" name="price" value="<?= $item['price'] ?? 0 ?>" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_available" name="is_available" 
                                   <?= ($item['is_available'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_available">
                                <strong>Disponible</strong> - El producto será visible para los clientes
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Guardar Cambios
                            </button>
                            <a href="/grg/admin/menus/<?= $item['category_id'] ?>/edit" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-info"></i> Información</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td><?= $item['id'] ?></td>
                        </tr>
                        <tr>
                            <td><strong>Categoría:</strong></td>
                            <td><?= htmlspecialchars($item['category_name'] ?? 'N/A') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Precio:</strong></td>
                            <td><strong>$<?= number_format($item['price'] ?? 0, 2) ?></strong></td>
                        </tr>
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>
                                <span class="badge bg-<?= ($item['is_available'] ?? true) ? 'success' : 'danger' ?>">
                                    <?= ($item['is_available'] ?? true) ? 'Disponible' : 'No disponible' ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                    
                    <hr>
                    
                    <button class="btn btn-danger w-100" onclick="deleteItem()">
                        <i class="bi bi-trash"></i> Eliminar Producto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $scripts = ob_start(); ?>
<script>
function deleteItem() {
    if (!confirm('¿Estás seguro de eliminar este producto? Esta acción no se puede deshacer.')) return;
    
    fetchWithCsrf('/grg/admin/menu-items/<?= $item['id'] ?>/delete', {
        method: 'POST'
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/grg/admin/menus/<?= $item['category_id'] ?>/edit';
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
