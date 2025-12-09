<?php ob_start(); ?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Restaurantes</h1>
        </div>
        <div class="col-md-4">
            <form method="GET" action="/grg/restaurants" class="d-flex">
                <input type="text" class="form-control me-2" name="search" placeholder="Buscar..." value="<?= htmlspecialchars($keyword ?? '') ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <?php if (!empty($restaurants)): ?>
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= $restaurant['image_url'] ?? '/grg/public/assets/img/restaurant-default.svg' ?>" 
                             class="card-img-top" alt="<?= htmlspecialchars($restaurant['name']) ?>"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($restaurant['name']) ?></h5>
                            <p class="card-text text-muted">
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restaurant['city']) ?><br>
                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($restaurant['phone']) ?>
                            </p>
                            <p class="card-text"><?= htmlspecialchars(substr($restaurant['description'] ?? '', 0, 100)) ?>...</p>
                        </div>
                        <div class="card-footer bg-white">
                            <a href="/grg/restaurants/<?= $restaurant['id'] ?>" class="btn btn-primary w-100">
                                <i class="bi bi-calendar-plus"></i> Reservar
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No se encontraron restaurantes.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
