<?php ob_start(); ?>
<div class="container my-5">
        <h1 class="mb-4"><i class="bi bi-receipt"></i> Mis Pedidos</h1>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> No tienes pedidos aún.
                <a href="/grg/restaurants" class="alert-link">Explorar restaurantes</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><strong>Pedido #<?= $order['id'] ?></strong></span>
                                <span class="badge bg-success">Enviado</span>
                            </div>
                            <div class="card-body">
                                <p class="mb-1">
                                    <i class="bi bi-shop"></i> 
                                    <strong><?= htmlspecialchars($order['restaurant_name']) ?></strong>
                                </p>
                                <p class="mb-1 text-muted">
                                    <i class="bi bi-calendar"></i> 
                                    <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                </p>
                                <p class="mb-1 text-muted">
                                    <i class="bi bi-telephone"></i> 
                                    <?= htmlspecialchars(isset($order['phone']) ? $order['phone'] : '') ?>
                                </p>
                                <p class="mb-3">
                                    <i class="bi bi-cash"></i> 
                                    <?= $order['payment_method'] === 'EFECTIVO' ? 'Efectivo' : 'Otros' ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="text-primary mb-0">$<?= number_format($order['total'], 2) ?></h5>
                                    <a href="/grg/orders/<?= $order['id'] ?>" class="btn btn-sm btn-primary">
                                        Ver Detalle
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
