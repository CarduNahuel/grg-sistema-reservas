<?php ob_start(); ?>
<div class="container my-5">
        <h1 class="mb-4"><i class="bi bi-cart3"></i> Mi Carrito</h1>

        <?php if (empty($items)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Tu carrito está vacío.
                <a href="/grg/restaurants" class="alert-link">Ver restaurantes</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Pedido de <?= htmlspecialchars($cart['restaurant_name']) ?></h5>
                            <form action="/grg/cart/clear" method="POST" onsubmit="return confirm('¿Vaciar el carrito?')">
                                <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i> Vaciar
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            <?php foreach ($items as $item): ?>
                                <div class="row mb-3 pb-3 border-bottom">
                                    <div class="col-md-2">
                                        <?php if ($item['image_url']): ?>
                                            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                 class="img-fluid rounded" 
                                                 alt="">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                                 style="height: 80px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-10">
                                        <h5><?= htmlspecialchars($item['product_name']) ?></h5>
                                        <p class="text-muted small mb-1">$<?= number_format($item['base_price'], 2) ?></p>
                                        
                                        <?php if (!empty($item['options'])): ?>
                                            <div class="small text-muted mb-2">
                                                <?php foreach ($item['options'] as $opt): ?>
                                                    <div>• <?= htmlspecialchars($opt['option_name']) ?>: <?= htmlspecialchars($opt['value_label']) ?>
                                                        <?php if ($opt['extra_price'] > 0): ?>
                                                            (+$<?= number_format($opt['extra_price'], 2) ?>)
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($item['note']): ?>
                                            <p class="small text-muted mb-2">
                                                <i class="bi bi-chat-left-text"></i> <?= htmlspecialchars($item['note']) ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="btn-group">
                                                <form action="/grg/cart/update" method="POST" class="d-inline">
                                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                    <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                                    <input type="hidden" name="quantity" value="<?= max(1, $item['quantity'] - 1) ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-dash"></i>
                                                    </button>
                                                </form>
                                                <span class="btn btn-sm btn-outline-secondary disabled"><?= $item['quantity'] ?></span>
                                                <form action="/grg/cart/update" method="POST" class="d-inline">
                                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                    <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                                    <input type="hidden" name="quantity" value="<?= $item['quantity'] + 1 ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-plus"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <div>
                                                <strong class="text-primary">$<?= number_format($item['total_price'], 2) ?></strong>
                                                <form action="/grg/cart/remove" method="POST" class="d-inline ms-2">
                                                    <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                                    <input type="hidden" name="cart_item_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Resumen</h5>
                        </div>
                        <div class="card-body">
                            <form action="/grg/cart/send" method="POST" id="sendOrderForm">
                                <?= \App\Services\CSRFProtection::getTokenInput() ?>
                                <div class="mb-3">
                                    <label class="form-label">Teléfono de contacto *</label>
                                    <input type="tel" name="phone" class="form-control" 
                                           value="<?= htmlspecialchars($_SESSION['user_phone'] ?? '') ?>" 
                                           placeholder="+54 9 11 1234-5678" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Método de pago *</label>
                                    <select name="payment_method" class="form-select" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="EFECTIVO">Efectivo</option>
                                        <option value="OTROS">Otros</option>
                                    </select>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong>$<?= number_format($total, 2) ?></strong>
                                </div>

                                <div class="d-grid gap-2 mt-3">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-send"></i> Enviar Pedido
                                    </button>
                                    <a href="/grg/restaurants/<?= $cart['restaurant_id'] ?>/menu" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Seguir Comprando
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
</div>

<?php $content = ob_get_clean(); ?>
<script>
    document.getElementById('sendOrderForm')?.addEventListener('submit', function(e) {
        if (!confirm('¿Confirmar el envío del pedido?')) {
            e.preventDefault();
        }
    });
</script>
<?php include __DIR__ . '/../layouts/app.php'; ?>
