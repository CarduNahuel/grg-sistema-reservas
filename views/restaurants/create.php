<?php ob_start(); ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="bi bi-shop"></i> Crear Nuevo Restaurante</h3>
                </div>
                <div class="card-body">
                    <form action="/grg/owner/restaurants" method="POST" enctype="multipart/form-data">
                        <?= \App\Services\CSRFProtection::getTokenInput() ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre del Restaurante *</label>
                            <input type="text" name="name" class="form-control" required 
                                   placeholder="Ej: La Parrilla de Don Juan">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Descripción *</label>
                            <textarea name="description" class="form-control" rows="4" required 
                                      placeholder="Describe tu restaurante, especialidades, ambiente, etc."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Dirección *</label>
                                    <input type="text" name="address" class="form-control" required 
                                           placeholder="Ej: Av. Corrientes 1234">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Ciudad *</label>
                                    <input type="text" name="city" class="form-control" required 
                                           placeholder="Ej: Buenos Aires">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Provincia/Estado</label>
                                    <input type="text" name="state" class="form-control" 
                                           placeholder="Ej: CABA">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Teléfono *</label>
                                    <input type="tel" name="phone" class="form-control" required 
                                           placeholder="Ej: +54 11 1234-5678">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required 
                                   placeholder="Ej: contacto@restaurante.com">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Horario de Apertura *</label>
                                    <input type="time" name="opening_time" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Horario de Cierre *</label>
                                    <input type="time" name="closing_time" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Imagen del Restaurante</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Formatos permitidos: JPG, PNG. Máximo 5MB.</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-plus-circle"></i> Crear Restaurante
                            </button>
                            <a href="/grg/owner/dashboard" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/app.php'; ?>
