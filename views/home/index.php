<?php ob_start(); ?>

<div class="container">
    <!-- Hero Section -->
    <div class="row py-5">
        <div class="col-lg-6">
            <h1 class="display-4 fw-bold">Reserva tu mesa favorita</h1>
            <p class="lead">Encuentra y reserva en los mejores restaurantes de tu ciudad de manera rápida y sencilla.</p>
            <div class="d-grid gap-2 d-md-flex">
                <a href="/grg/restaurants" class="btn btn-primary btn-lg px-4">Ver Restaurantes</a>
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <a href="/grg/auth/register" class="btn btn-outline-secondary btn-lg px-4">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-6">
            <img src="/grg/public/assets/img/hero-image.jpg" alt="Restaurante" class="img-fluid rounded" 
                 onerror="this.src='https://via.placeholder.com/600x400?text=Restaurantes'">
        </div>
    </div>

    <!-- Features -->
    <div class="row py-5 text-center">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-search display-4 text-primary"></i>
                    <h3 class="mt-3">Busca</h3>
                    <p>Explora una amplia selección de restaurantes en tu ciudad.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-calendar-check display-4 text-primary"></i>
                    <h3 class="mt-3">Reserva</h3>
                    <p>Selecciona tu mesa y horario preferido de forma instantánea.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <i class="bi bi-emoji-smile display-4 text-primary"></i>
                    <h3 class="mt-3">Disfruta</h3>
                    <p>Llega y disfruta de tu experiencia gastronómica sin preocupaciones.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Featured Restaurants -->
    <div class="row py-5">
        <div class="col-12">
            <h2 class="mb-4">Restaurantes Destacados</h2>
        </div>
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
                                <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($restaurant['city']) ?>
                            </p>
                            <p class="card-text"><?= htmlspecialchars(substr($restaurant['description'] ?? '', 0, 100)) ?>...</p>
                            <a href="/grg/restaurants/<?= $restaurant['id'] ?>" class="btn btn-primary">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No hay restaurantes disponibles en este momento.</div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($restaurants)): ?>
        <div class="row">
            <div class="col-12 text-center">
                <a href="/grg/restaurants" class="btn btn-outline-primary btn-lg">Ver Todos los Restaurantes</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
