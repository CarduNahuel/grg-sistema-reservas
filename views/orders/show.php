<?php ob_start(); ?>
<div class="container my-5">
        <div class="row mb-3">
            <div class="col">
                <h1><i class="bi bi-receipt"></i> Pedido #<?= $order['id'] ?></h1>
            </div>
            <div class="col-auto">
                <a href="/grg/orders" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Productos</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($items as $item): ?>
                            <div class="row mb-3 pb-3 border-bottom">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6><?= htmlspecialchars($item['product_name']) ?> x<?= $item['quantity'] ?></h6>
                                            <p class="text-muted small mb-0">$<?= number_format($item['base_price'], 2) ?> c/u</p>
                                            
                                            <?php if (!empty($item['options'])): ?>
                                                <div class="small text-muted mt-1">
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
                                                <p class="small text-muted mt-1 mb-0">
                                                    <i class="bi bi-chat-left-text"></i> <?= htmlspecialchars($item['note']) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <strong>$<?= number_format($item['total_price'], 2) ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Información</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Restaurante:</strong><br>
                            <?= htmlspecialchars($order['restaurant_name']) ?>
                        </p>
                        <p class="mb-2">
                            <strong>Cliente:</strong><br>
                            <?= htmlspecialchars($order['customer_name']) ?>
                        </p>
                        <p class="mb-2">
                            <strong>Email:</strong><br>
                            <?= htmlspecialchars($order['customer_email']) ?>
                        </p>
                        <p class="mb-2">
                            <strong>Teléfono:</strong><br>
                            <?= htmlspecialchars($order['phone'] ?? 'No especificado') ?>
                        </p>
                        <p class="mb-2">
                            <strong>Fecha:</strong><br>
                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                        </p>
                        <p class="mb-2">
                            <strong>Método de pago:</strong><br>
                            <?= $order['payment_method'] === 'EFECTIVO' ? 'Efectivo' : 'Otros' ?>
                        </p>
                        <p class="mb-2">
                            <strong>Estado:</strong><br>
                            <span class="badge bg-success" id="order-status-badge">
                                <?php
                                $statusLabels = [
                                    'enviado' => 'Enviado',
                                    'en_preparacion' => 'En preparación',
                                    'listo' => 'Listo',
                                    'entregado' => 'Completado/Pagado',
                                    'cancelado' => 'Cancelado'
                                ];
                                echo $statusLabels[$order['status']] ?? 'Enviado';
                                ?>
                            </span>
                        </p>
                        
                        <?php if ($order['status'] !== 'entregado' && $order['status'] !== 'cancelado'): ?>
                        <div class="mt-3">
                            <button class="btn btn-success btn-sm w-100 mb-2" onclick="updateOrderStatus(<?= $order['id'] ?>, 'entregado')">
                                <i class="bi bi-check-circle"></i> Pedido Completado/Pagado
                            </button>
                            <button class="btn btn-danger btn-sm w-100" onclick="updateOrderStatus(<?= $order['id'] ?>, 'cancelado')">
                                <i class="bi bi-x-circle"></i> Cancelar Pedido
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <h4 class="text-primary mb-0">$<?= number_format($order['total'], 2) ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateOrderStatus(orderId, newStatus) {
    if (!confirm('¿Confirmar cambio de estado?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);
    
    fetch('/grg/api/orders/update-status', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Estado actualizado correctamente');
            location.reload();
        } else {
            alert('Error al actualizar estado: ' + (data.message || 'Error desconocido'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud');
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
