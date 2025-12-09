<?php ob_start(); ?>

<style>
.zone-btn { display: inline-block; margin: 4px 6px 0 0; padding: 6px 10px; border: 1px solid #dee2e6; border-radius: 6px; background: #fff; cursor: pointer; transition: all 0.2s; font-size: 13px; }
.zone-btn.active { background: #0ea5e9; color: white; border-color: #0ea5e9; font-weight: bold; }
.zone-btn:hover { background: #e7f3ff; border-color: #0ea5e9; }
.mesa-disponible { filter: none; }
.mesa-ocupada { filter: grayscale(0.7); opacity: 0.6; }
.mesa-seleccionada { background: #fde68a !important; border-color: #f59e0b !important; box-shadow: 0 0 0 2px #fcd34d inset; }
</style>

<div class="container">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <img src="<?= $restaurant['image_url'] ?? '/grg/public/assets/img/restaurant-default.svg' ?>" 
                     class="card-img-top" alt="<?= htmlspecialchars($restaurant['name']) ?>"
                     style="height: 300px; object-fit: cover;">
                <div class="card-body">
                    <h1 class="card-title"><?= htmlspecialchars($restaurant['name']) ?></h1>
                    <p class="lead"><?= htmlspecialchars($restaurant['description'] ?? '') ?></p>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="bi bi-geo-alt"></i> Ubicación</h5>
                            <p><?= htmlspecialchars($restaurant['address']) ?><br>
                               <?= htmlspecialchars($restaurant['city']) ?>
                               <?php if ($restaurant['state']): ?>, <?= htmlspecialchars($restaurant['state']) ?><?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="bi bi-telephone"></i> Contacto</h5>
                            <p>Teléfono: <?= htmlspecialchars($restaurant['phone']) ?><br>
                               <?php if ($restaurant['email']): ?>
                                   Email: <?= htmlspecialchars($restaurant['email']) ?>
                               <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="bi bi-clock"></i> Horario</h5>
                            <p><?= date('H:i', strtotime($restaurant['opening_time'])) ?> - 
                               <?= date('H:i', strtotime($restaurant['closing_time'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <a href="/grg/restaurants/<?= $restaurant['id'] ?>/menu" class="btn btn-success btn-sm">
                                <i class="bi bi-book"></i> Ver Menú
                            </a>
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == ($restaurant['owner_id'] ?? null)): ?>
                                <a href="<?= url('/owner/restaurants/' . $restaurant['id'] . '/plano') ?>" class="btn btn-warning btn-sm">
                                    <i class="bi bi-grid-3x3"></i> Configurar Plano
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plano para seleccionar mesa -->
            <div class="card shadow mt-4">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3>Plano</h3>
                            <p class="text-muted mb-0">Elige una mesa disponible en la zona actual</p>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= url('/restaurants/' . $restaurant['id'] . '/plano') ?>" target="_blank">Ver en pantalla completa</a>
                    </div>

                    <?php
                    // Usar el mismo plano partial que en create
                    $gridId = 'restaurantShow' . $restaurant['id'];
                    $onCellClick = 'selectTableFromPlanoShow_' . $gridId . '(TABLEID)';
                    $selectable = true;
                    include __DIR__ . '/../partials/plano_grid.php';
                    ?>

                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div id="plano-info" class="text-muted">Ninguna mesa seleccionada</div>
                        <button class="btn btn-primary btn-sm" id="plano-reservar" disabled onclick="planoReservar()">Reservar mesa</button>
                    </div>

                    <div class="mt-3" style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <span class="badge" style="background:#d1fae5; color:#0f5132;">🪑 Mesa</span>
                        <span class="badge" style="background:#ddd6fe; color:#4c1d95;">⬆️ Escalera</span>
                        <span class="badge" style="background:#bfdbfe; color:#1d4ed8;">💧 Baño</span>
                        <span class="badge" style="background:#fed7aa; color:#b45309;">☕ Barra</span>
                        <span class="badge" style="background:#fecaca; color:#b91c1c;">🚪 Puerta</span>
                        <span class="badge" style="background:#94a3b8; color:#111827;">⬛ Pared</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h4>Hacer una Reserva</h4>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/grg/reservations/create/<?= $restaurant['id'] ?>" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-calendar-plus"></i> Reservar Ahora
                        </a>
                    <?php else: ?>
                        <p class="text-muted">Debes iniciar sesión para hacer una reserva.</p>
                        <a href="/grg/auth/login" class="btn btn-primary w-100">Iniciar Sesión</a>
                        <a href="/grg/auth/register" class="btn btn-outline-primary w-100 mt-2">Registrarse</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<script>
let planoMesaSeleccionada = null;
let planoCeldaSeleccionada = null;

function selectTableFromPlanoShow_restaurantShow<?= $restaurant['id'] ?>(tableId) {
    const cell = document.querySelector(`[data-id="${tableId}"]`);
    if (cell && cell.dataset.tipo === 'mesa' && cell.dataset.disponible === '1') {
        if (planoCeldaSeleccionada) {
            planoCeldaSeleccionada.classList.remove('mesa-seleccionada');
        }
        cell.classList.add('mesa-seleccionada');
        planoCeldaSeleccionada = cell;
        
        const zona = cell.dataset.zona || 'General';
        planoMesaSeleccionada = {
            id: cell.dataset.id,
            numero: cell.dataset.numero,
            capacidad: cell.dataset.capacidad,
            zona
        };
        document.getElementById('plano-info').textContent = `Mesa ${planoMesaSeleccionada.numero || planoMesaSeleccionada.id} - Capacidad ${planoMesaSeleccionada.capacidad || 'N/D'} - Zona ${zona}`;
        document.getElementById('plano-reservar').disabled = false;
    }
}

function planoReservar() {
    if (!planoMesaSeleccionada) return;
    window.location.href = '/grg/reservations/create/<?= $restaurant['id'] ?>?table_id=' + encodeURIComponent(planoMesaSeleccionada.id);
}
</script>

<?php include __DIR__ . '/../layouts/app.php'; ?>
