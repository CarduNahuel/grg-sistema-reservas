<?php ob_start(); ?>

<div class="container-fluid my-5">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-book"></i> Gestionar Menú</h1>
            <p class="text-muted">Organiza tus categorías y productos</p>
        </div>
        <div class="col-auto">
            <a href="/grg/owner/restaurants/<?= $restaurant_id ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Nueva Categoría</h5>
                </div>
                <div class="card-body">
                    <form action="/grg/owner/menu/category/store" method="POST">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        <input type="hidden" name="restaurant_id" value="<?= $restaurant_id ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="name" class="form-control" required 
                                   placeholder="Ej: Entradas, Platos Principales">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="2" 
                                      placeholder="Describe esta categoría"></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus"></i> Crear Categoría
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Nuevo Producto</h5>
                </div>
                <div class="card-body">
                    <form action="/grg/owner/menu/item/store" method="POST" enctype="multipart/form-data">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        <input type="hidden" name="restaurant_id" value="<?= $restaurant_id ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Categoría *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre *</label>
                            <input type="text" name="name" class="form-control" required 
                                   placeholder="Ej: Bife de Chorizo">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea name="description" class="form-control" rows="2" 
                                      placeholder="Describe el producto"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Precio *</label>
                            <input type="number" name="price" class="form-control" required 
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Imagen</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-plus"></i> Crear Producto
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Categorías -->
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Categorías</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <p class="text-muted">No hay categorías aún.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                            <td><?= htmlspecialchars(substr($category['description'] ?? '', 0, 50)) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $category['is_active'] ? 'success' : 'danger' ?>">
                                                    <?= $category['is_active'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form action="/grg/owner/menu/category/toggle" method="POST" class="d-inline">
                                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </button>
                                                </form>
                                                <form action="/grg/owner/menu/category/delete" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('¿Eliminar esta categoría?')">
                                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Productos -->
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-tags"></i> Productos</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($items)): ?>
                        <p class="text-muted">No hay productos aún.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Precio</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars(substr($item['description'] ?? '', 0, 40)) ?></small>
                                            </td>
                                            <td>
                                                <?php 
                                                $cat = null;
                                                foreach ($categories as $c) {
                                                    if ($c['id'] == $item['category_id']) {
                                                        $cat = $c;
                                                        break;
                                                    }
                                                }
                                                echo $cat ? htmlspecialchars($cat['name']) : 'N/A';
                                                ?>
                                            </td>
                                            <td>$<?= number_format($item['price'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $item['is_active'] ? 'success' : 'danger' ?>">
                                                    <?= $item['is_active'] ? 'Activo' : 'Inactivo' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <form action="/grg/owner/menu/item/toggle" method="POST" class="d-inline">
                                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-toggle-on"></i>
                                                    </button>
                                                </form>
                                                <form action="/grg/owner/menu/item/delete" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('¿Eliminar este producto?')">
                                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
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
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/app.php'; ?>
