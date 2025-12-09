<?php 
$title = 'Editar Restaurante'; 
ob_start(); 
?>

<div class="mb-4">
    <a href="<?= url('/admin/restaurants') ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Volver a Restaurantes
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pencil"></i> Editar Restaurante
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/admin/restaurants/' . $restaurant['id'] . '/update') ?>">
                    <?= \App\Services\CSRFProtection::getTokenInput() ?>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre del Restaurante *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($restaurant['name']) ?>" 
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= htmlspecialchars($restaurant['email']) ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono *</label>
                            <input type="tel" 
                                   class="form-control" 
                                   id="phone" 
                                   name="phone" 
                                   value="<?= htmlspecialchars($restaurant['phone']) ?>" 
                                   required>
                        </div>
                        <div class="col-md-6">
                            <label for="address" class="form-label">Dirección *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="address" 
                                   name="address" 
                                   value="<?= htmlspecialchars($restaurant['address']) ?>" 
                                   required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">Ciudad *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="city" 
                                   name="city" 
                                   value="<?= htmlspecialchars($restaurant['city']) ?>" 
                                   required>
                        </div>
                        <div class="col-md-3">
                            <label for="state" class="form-label">Provincia</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="state" 
                                   name="state" 
                                   value="<?= htmlspecialchars($restaurant['state'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="postal_code" class="form-label">Código Postal</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="postal_code" 
                                   name="postal_code" 
                                   value="<?= htmlspecialchars($restaurant['postal_code'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" 
                                  id="description" 
                                  name="description" 
                                  rows="4"><?= htmlspecialchars($restaurant['description'] ?? '') ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="opening_time" class="form-label">Hora de Apertura</label>
                            <input type="time" 
                                   class="form-control" 
                                   id="opening_time" 
                                   name="opening_time" 
                                   value="<?= htmlspecialchars($restaurant['opening_time'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="closing_time" class="form-label">Hora de Cierre</label>
                            <input type="time" 
                                   class="form-control" 
                                   id="closing_time" 
                                   name="closing_time" 
                                   value="<?= htmlspecialchars($restaurant['closing_time'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="requires_payment" 
                               name="requires_payment" 
                               value="1"
                               <?= $restaurant['requires_payment'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="requires_payment">
                            Requiere Pago
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>ID:</strong> <?= $restaurant['id'] ?>
                </p>
                <p class="mb-2">
                    <strong>Estado:</strong>
                    <?php if ($restaurant['is_active']): ?>
                        <span class="badge bg-success">Activo</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactivo</span>
                    <?php endif; ?>
                </p>
                <p class="mb-2">
                    <strong>Creado:</strong> 
                    <br>
                    <small><?= date('d/m/Y H:i', strtotime($restaurant['created_at'])) ?></small>
                </p>
                <p class="mb-0">
                    <strong>Actualizado:</strong> 
                    <br>
                    <small><?= date('d/m/Y H:i', strtotime($restaurant['updated_at'])) ?></small>
                </p>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean(); 
require __DIR__ . '/../layouts/app.php'; 
?>
