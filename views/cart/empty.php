<?php ob_start(); ?>
<div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <i class="bi bi-cart-x" style="font-size: 6rem; color: #ccc;"></i>
                <h2 class="mt-4">Tu carrito está vacío</h2>
                <p class="text-muted">Explora nuestros restaurantes y agrega productos a tu carrito.</p>
                <a href="/grg/restaurants" class="btn btn-primary mt-3">
                    <i class="bi bi-shop"></i> Ver Restaurantes
                </a>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
