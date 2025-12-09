<?php ob_start(); ?>

<div class="container">
    <h1 class="mb-4">Panel de Administración</h1>
    
    <div class="alert alert-info">
        <strong>Bienvenido, <?= htmlspecialchars($user['first_name']) ?>!</strong> 
        <p class="mb-0">Tienes acceso completo al sistema como Superadministrador.</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow stat-card">
                <div class="card-body">
                    <h6 class="text-muted">Total Usuarios</h6>
                    <h2><i class="bi bi-people"></i> <?= $stats['total_users'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow stat-card success">
                <div class="card-body">
                    <h6 class="text-muted">Restaurantes Activos</h6>
                    <h2><i class="bi bi-shop"></i> <?= $stats['total_restaurants'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow stat-card info">
                <div class="card-body">
                    <h6 class="text-muted">Total Reservas</h6>
                    <h2><i class="bi bi-calendar-check"></i> <?= $stats['total_reservations'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow stat-card warning">
                <div class="card-body">
                    <h6 class="text-muted">Reservas Pendientes</h6>
                    <h2><i class="bi bi-clock"></i> <?= $stats['pending_reservations'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Gestión Completa del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 mb-3">
                            <a href="<?= url('/admin/users') ?>" class="btn btn-outline-primary btn-lg w-100 h-100">
                                <i class="bi bi-people fs-3"></i><br>
                                <small>Gestionar Usuarios</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="<?= url('/admin/restaurants') ?>" class="btn btn-outline-success btn-lg w-100 h-100">
                                <i class="bi bi-shop fs-3"></i><br>
                                <small>Gestionar Restaurantes</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="<?= url('/admin/menus') ?>" class="btn btn-outline-warning btn-lg w-100 h-100">
                                <i class="bi bi-book fs-3"></i><br>
                                <small>Gestionar Menús</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="<?= url('/admin/reservations') ?>" class="btn btn-outline-info btn-lg w-100 h-100">
                                <i class="bi bi-calendar-check fs-3"></i><br>
                                <small>Gestionar Reservas</small>
                            </a>
                        </div>
                        <div class="col-md-2 mb-3">
                            <a href="<?= url('/admin/orders') ?>" class="btn btn-outline-danger btn-lg w-100 h-100">
                                <i class="bi bi-receipt fs-3"></i><br>
                                <small>Gestionar Pedidos</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <td><strong>Versión:</strong></td>
                            <td>GRG v1.0 MVP</td>
                        </tr>
                        <tr>
                            <td><strong>Base de Datos:</strong></td>
                            <td>MySQL (Puerto 3307)</td>
                        </tr>
                        <tr>
                            <td><strong>Entorno:</strong></td>
                            <td><?= $_ENV['APP_ENV'] ?? 'development' ?></td>
                        </tr>
                        <tr>
                            <td><strong>PHP:</strong></td>
                            <td><?= phpversion() ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-tools"></i> Herramientas de Administración</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="<?= url('/admin/users') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-people-fill"></i> Administrar Usuarios
                        </a>
                        <a href="<?= url('/admin/restaurants') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-shop"></i> Administrar Restaurantes
                        </a>
                        <a href="<?= url('/admin/menus') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-book"></i> Administrar Menús y Productos
                        </a>
                        <a href="<?= url('/admin/reservations') ?>" class="list-group-item list-group-item-action">
                            <i class="bi bi-calendar-check"></i> Administrar Reservas
                        </a>
                        <a href="/grg/notifications" class="list-group-item list-group-item-action">
                            <i class="bi bi-bell"></i> Centro de Notificaciones
                        </a>
                        <a href="/grg/profile" class="list-group-item list-group-item-action">
                            <i class="bi bi-person-circle"></i> Mi Perfil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-activity"></i> Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">El panel de actividad estará disponible próximamente.</p>
                    <p class="mb-0">
                        <small>Aquí podrás ver un registro de las acciones recientes en el sistema.</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/app.php'; 
?>
