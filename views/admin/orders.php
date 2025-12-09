<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Gestionar Pedidos' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/grg/">
                <i class="bi bi-calendar-check"></i> GRG
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/grg/restaurants">Restaurantes</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/grg/admin">
                                <i class="bi bi-speedometer2"></i> Admin
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="/grg/profile">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/grg/logout">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-receipt"></i> Gestionar Pedidos</h2>
            <a href="/grg/admin" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver al Panel
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <select name="status" class="form-select">
                            <option value="">Todos</option>
                            <option value="enviado" <?= $statusFilter === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                            <option value="entregado" <?= $statusFilter === 'entregado' ? 'selected' : '' ?>>Completado</option>
                            <option value="cancelado" <?= $statusFilter === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Restaurante</label>
                        <select name="restaurant" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($restaurants as $rest): ?>
                                <option value="<?= $rest['id'] ?>" <?= $restaurantFilter == $rest['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($rest['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-funnel"></i> Filtrar
                        </button>
                        <a href="/grg/admin/orders" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Restaurante</th>
                                <th>Cliente</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay pedidos</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $order): 
                                    // Default to 'enviado' if status is null or empty
                                    $currentStatus = !empty($order['status']) ? $order['status'] : 'enviado';
                                    
                                    $statusColors = [
                                        'enviado' => 'primary',
                                        'en_preparacion' => 'primary',
                                        'listo' => 'primary',
                                        'entregado' => 'success',
                                        'cancelado' => 'danger'
                                    ];
                                    $statusLabels = [
                                        'enviado' => 'Enviado',
                                        'en_preparacion' => 'Enviado',
                                        'listo' => 'Enviado',
                                        'entregado' => 'Completado',
                                        'cancelado' => 'Cancelado'
                                    ];
                                    $badgeColor = $statusColors[$currentStatus] ?? 'secondary';
                                    $statusLabel = $statusLabels[$currentStatus] ?? 'Estado: ' . $currentStatus;
                                ?>
                                    <tr>
                                        <td><strong>#<?= $order['id'] ?></strong></td>
                                        <td><?= htmlspecialchars($order['restaurant_name']) ?></td>
                                        <td><?= htmlspecialchars($order['customer_name'] ?? 'Invitado') ?></td>
                                        <td><?= $order['items_count'] ?> items</td>
                                        <td><strong>$<?= number_format($order['total'], 2) ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= $badgeColor ?>">
                                                <?= $statusLabel ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                        <td>
                                            <a href="/grg/orders/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary" title="Ver Detalles">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <?php if ($currentStatus !== 'entregado' && $currentStatus !== 'cancelado'): ?>
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        data-order-id="<?= $order['id'] ?>"
                                                        data-action="complete"
                                                        title="Completar Pedido #<?= $order['id'] ?>"
                                                        onclick="completeOrder(<?= $order['id'] ?>)">
                                                    <i class="bi bi-check-circle"></i> Completar
                                                </button>
                                                
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-order-id="<?= $order['id'] ?>"
                                                        data-action="cancel"
                                                        title="Cancelar Pedido #<?= $order['id'] ?>"
                                                        onclick="cancelOrder(<?= $order['id'] ?>)">
                                                    <i class="bi bi-x-circle"></i> Cancelar
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">
                                                    <i class="bi bi-check-lg"></i> Finalizado
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function completeOrder(orderId) {
        if (!confirm(`¿Completar pedido #${orderId}?`)) {
            return;
        }
        
        updateOrderStatus(orderId, 'entregado', 'COMPLETADO');
    }
    
    function cancelOrder(orderId) {
        if (!confirm(`¿Cancelar pedido #${orderId}?`)) {
            return;
        }
        
        updateOrderStatus(orderId, 'cancelado', 'CANCELADO');
    }
    
    function updateOrderStatus(orderId, newStatus, label) {
        console.log('Updating order', orderId, 'to status', newStatus);
        
        const data = new URLSearchParams();
        data.append('order_id', orderId);
        data.append('status', newStatus);
        
        console.log('Sending to /grg/api/orders/update-status');
        
        fetch('/grg/api/orders/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: data.toString()
        })
        .then(response => {
            console.log('Response received, status:', response.status);
            
            if (!response.ok && response.status !== 302) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Try to parse as JSON
            return response.text().then(text => {
                console.log('Response text:', text);
                if (text) {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Failed to parse JSON:', e);
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                }
                return { success: false, message: 'Empty response' };
            });
        })
        .then(data => {
            console.log('Parsed data:', data);
            
            if (data.success) {
                alert('✓ Pedido #' + orderId + ' marcado como ' + label);
                setTimeout(() => location.reload(), 500);
            } else {
                alert('Error: ' + (data.message || 'No se pudo actualizar'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }
    </script>
