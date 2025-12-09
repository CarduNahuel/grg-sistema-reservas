<?php ob_start(); ?>

<div class="container-fluid my-5">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-shop"></i> Mis Restaurantes</h1>
            <p class="text-muted">Gestiona todos tus establecimientos</p>
        </div>
        <div class="col-auto">
            <a href="/grg/owner/restaurants/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Crear Restaurante
            </a>
        </div>
    </div>

    <?php if (empty($restaurants)): ?>
        <div class="card text-center py-5">
            <div class="card-body">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <h5 class="mt-3 text-muted">No tienes restaurantes aún</h5>
                <p class="text-muted">Comienza creando tu primer restaurante</p>
                <a href="/grg/owner/restaurants/create" class="btn btn-primary mt-3">
                    <i class="bi bi-plus-circle"></i> Crear Restaurante
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card shadow-sm h-100 hover-card">
                        <?php if (!empty($restaurant['image'])): ?>
                            <img src="<?= htmlspecialchars($restaurant['image']) ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($restaurant['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restaurant['city'] ?? 'Sin ciudad') ?>
                            </p>
                            <p class="card-text" style="font-size: 0.9rem;">
                                <?= htmlspecialchars(substr($restaurant['description'] ?? '', 0, 80)) ?>...
                            </p>
                            
                            <div class="d-flex gap-2 mb-3">
                                <span class="badge bg-<?= $restaurant['is_active'] ? 'success' : 'danger' ?>">
                                    <?= $restaurant['is_active'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                                <?php if ($restaurant['requires_payment'] && $restaurant['payment_status'] !== 'paid'): ?>
                                    <span class="badge bg-warning">Pago Pendiente</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-white border-top">
                            <div class="btn-group w-100" role="group">
                                <a href="/grg/owner/restaurants/<?= $restaurant['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Dashboard">
                                    <i class="bi bi-speedometer2"></i>
                                </a>
                                <a href="/grg/owner/restaurants/<?= $restaurant['id'] ?>/menu" class="btn btn-sm btn-outline-success" title="Menú">
                                    <i class="bi bi-book"></i>
                                </a>
                                <a href="/grg/owner/restaurants/<?= $restaurant['id'] ?>/edit" class="btn btn-sm btn-outline-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="/grg/owner/restaurants/<?= $restaurant['id'] ?>/delete" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este restaurante?')">
                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .hover-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/app.php'; ?>
