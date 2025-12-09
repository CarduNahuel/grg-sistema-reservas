<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'GRG - Gestor de Reservas Gastronómicas' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/grg/public/css/style.css">

    <?= $styles ?? '' ?>
    
    <!-- CSRF Token -->
    <?= \App\Services\CSRFProtection::getTokenMeta() ?>
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
                    <?php if (isset($_SESSION['user_id'])): 
                        $cartModel = new \App\Models\Cart();
                        $activeCart = $cartModel->getActiveCart($_SESSION['user_id']);
                        $cartItemCount = $activeCart ? $cartModel->getItemCount($activeCart['id']) : 0;
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/grg/reservations">Mis Reservas</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/grg/orders">
                                <i class="bi bi-receipt"></i> Mis Pedidos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="/grg/cart" id="cartLink">
                                <i class="bi bi-cart3"></i> Carrito
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                                      id="cartCount" style="<?= $cartItemCount > 0 ? '' : 'display: none;' ?>"><?= $cartItemCount ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): 
                        $authService = new \App\Services\AuthService();
                        $userModel = new \App\Models\User();
                        $notificationCount = $userModel->getUnreadNotificationCount($_SESSION['user_id']);
                    ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/grg/notifications">
                                <i class="bi bi-bell"></i>
                                <?php if ($notificationCount > 0): ?>
                                    <span class="badge bg-danger"><?= $notificationCount ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= $_SESSION['user_name'] ?? 'Usuario' ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/grg/dashboard">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a></li>
                                <li><a class="dropdown-item" href="/grg/profile">
                                    <i class="bi bi-person"></i> Mi Perfil
                                </a></li>
                                <?php if ($authService->isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/grg/owner/restaurants/create">
                                        <i class="bi bi-plus-circle"></i> Crear Restaurante
                                    </a></li>
                                    <li><a class="dropdown-item" href="/grg/owner/restaurants">
                                        <i class="bi bi-shop"></i> Restaurantes
                                    </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/grg/auth/logout">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/grg/auth/login">Iniciar Sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-2" href="/grg/auth/register">Registrarse</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="container mt-3">
            <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?> alert-dismissible fade show">
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['flash'][$type]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="py-4">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> GRG - Gestor de Reservas Gastronómicas. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS with cache-busting version -->
    <?php $appJsVersion = filemtime(__DIR__ . '/../../public/js/app.js'); ?>
    <script src="/grg/public/js/app.js?v=<?= $appJsVersion ?>"></script>
    
    <?= $scripts ?? '' ?>
</body>
</html>
