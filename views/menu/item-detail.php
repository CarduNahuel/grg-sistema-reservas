<div class="row">
    <div class="col-md-6">
        <?php if ($item['image_url']): ?>
            <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                 class="img-fluid rounded" 
                 alt="<?= htmlspecialchars($item['name']) ?>">
        <?php else: ?>
            <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                 style="height: 300px;">
                <i class="bi bi-image text-muted" style="font-size: 6rem;"></i>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <h3><?= htmlspecialchars($item['name']) ?></h3>
        <p class="text-muted"><?= htmlspecialchars($item['description'] ?? '') ?></p>
        <h4 class="text-primary mb-4">$<?= number_format($item['price'], 2) ?></h4>

        <form id="addToCartForm" method="POST">
            <?= \App\Services\CSRFProtection::getTokenInput() ?>
            <input type="hidden" name="menu_item_id" value="<?= $item['id'] ?>">
            <input type="hidden" name="restaurant_id" value="<?= $item['restaurant_id'] ?>">
            
            <?php if (!empty($item['options'])): ?>
                <?php foreach ($item['options'] as $option): ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <?= htmlspecialchars($option['name']) ?>
                            <?php if ($option['is_required']): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                        
                        <?php if ($option['selection_type'] === 'single'): ?>
                            <?php foreach ($option['values'] as $value): ?>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="radio" 
                                           name="options[<?= $option['id'] ?>]" 
                                           value="<?= $value['id'] ?>" 
                                           id="opt_<?= $value['id'] ?>"
                                           <?= $option['is_required'] ? 'required' : '' ?>>
                                    <label class="form-check-label" for="opt_<?= $value['id'] ?>">
                                        <?= htmlspecialchars($value['label']) ?>
                                        <?php if ($value['extra_price'] > 0): ?>
                                            <span class="text-primary">+$<?= number_format($value['extra_price'], 2) ?></span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($option['values'] as $value): ?>
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="options[<?= $option['id'] ?>][]" 
                                           value="<?= $value['id'] ?>" 
                                           id="opt_<?= $value['id'] ?>">
                                    <label class="form-check-label" for="opt_<?= $value['id'] ?>">
                                        <?= htmlspecialchars($value['label']) ?>
                                        <?php if ($value['extra_price'] > 0): ?>
                                            <span class="text-primary">+$<?= number_format($value['extra_price'], 2) ?></span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Cantidad</label>
                <input type="number" name="quantity" class="form-control" value="1" min="1" max="20" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Nota (opcional)</label>
                <textarea name="note" class="form-control" rows="2" placeholder="Ej: Sin cebolla, bien cocido, etc."></textarea>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-cart-plus"></i> Agregar al Carrito
                </button>
            <?php else: ?>
                <a href="/grg/login" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Inicia sesión para ordenar
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>
