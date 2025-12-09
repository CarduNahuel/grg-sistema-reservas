<?php ob_start(); ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body p-5 text-center">
                    <i class="bi bi-credit-card display-1 text-primary mb-4"></i>
                    <h2>Pago de Restaurante</h2>
                    <p class="lead">Activa tu restaurante adicional</p>
                    
                    <hr>
                    
                    <div class="mb-4">
                        <h4><?= htmlspecialchars($restaurant['name']) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($restaurant['address']) ?></p>
                    </div>
                    
                    <div class="alert alert-info">
                        <h5>Monto a pagar</h5>
                        <h2 class="mb-0">$<?= number_format($amount, 2) ?> USD</h2>
                        <small class="text-muted">Pago único por restaurante adicional</small>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle"></i> <strong>Nota:</strong> Esta es una versión de demostración. 
                        El pago se simulará automáticamente. En producción, integrar con una pasarela de pago real 
                        (Stripe, MercadoPago, PayPal, etc.).
                    </div>
                    
                    <form method="POST" action="/grg/payments/process">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        <input type="hidden" name="restaurant_id" value="<?= $restaurant['id'] ?>">
                        <input type="hidden" name="payment_method" value="credit_card">
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-check-circle"></i> Procesar Pago (Demo)
                        </button>
                    </form>
                    
                    <a href="/grg/owner/dashboard" class="btn btn-outline-secondary mt-3 w-100">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
