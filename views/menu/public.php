<?php ob_start(); ?>
<div class="container my-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-3">
                    <i class="bi bi-book"></i> <?= htmlspecialchars($restaurant['name']) ?>
                </h1>
                <p class="text-muted"><?= htmlspecialchars($restaurant['address']) ?></p>
            </div>
        </div>

        <?php if (empty($categories)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Este restaurante aún no tiene menú publicado.
            </div>
        <?php else: ?>
            <!-- Category Tabs -->
            <ul class="nav nav-tabs mb-4" role="tablist">
                <?php foreach ($categories as $index => $category): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" 
                                id="cat-<?= $category['id'] ?>-tab" 
                                data-bs-toggle="tab" 
                                data-bs-target="#cat-<?= $category['id'] ?>" 
                                type="button">
                            <?= htmlspecialchars($category['name']) ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Category Content -->
            <div class="tab-content">
                <?php foreach ($categories as $index => $category): ?>
                    <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" 
                         id="cat-<?= $category['id'] ?>" 
                         role="tabpanel">
                        
                        <?php if ($category['description']): ?>
                            <p class="text-muted"><?= htmlspecialchars($category['description']) ?></p>
                        <?php endif; ?>

                        <?php if (empty($category['items'])): ?>
                            <p class="text-muted">No hay productos en esta categoría.</p>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($category['items'] as $item): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 shadow-sm">
                                            <?php if ($item['image_url']): ?>
                                                <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                                                     class="card-img-top" 
                                                     style="height: 200px; object-fit: cover;" 
                                                     alt="<?= htmlspecialchars($item['name']) ?>">
                                            <?php else: ?>
                                                <div class="bg-light d-flex align-items-center justify-content-center" 
                                                     style="height: 200px;">
                                                    <i class="bi bi-image text-muted" style="font-size: 4rem;"></i>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                                <p class="card-text text-muted flex-grow-1">
                                                    <?= htmlspecialchars(substr($item['description'] ?? '', 0, 100)) ?>
                                                    <?= strlen($item['description'] ?? '') > 100 ? '...' : '' ?>
                                                </p>
                                                <div class="d-flex justify-content-between align-items-center mt-2">
                                                    <span class="h5 mb-0 text-primary">$<?= number_format($item['price'], 2) ?></span>
                                                    <button class="btn btn-primary btn-sm" 
                                                            onclick="viewItemDetail(<?= $item['id'] ?>)">
                                                        <i class="bi bi-cart-plus"></i> Agregar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Item Detail Modal -->
    <div class="modal fade" id="itemDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemModalTitle">Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="itemModalBody">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<script>
    function viewItemDetail(itemId) {
        const modal = new bootstrap.Modal(document.getElementById('itemDetailModal'));
        modal.show();

        fetch(`/grg/menu/item/${itemId}`)
            .then(r => r.text())
            .then(html => {
                document.getElementById('itemModalBody').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('itemModalBody').innerHTML = 
                    '<div class="alert alert-danger">Error al cargar el producto.</div>';
            });
    }

    // Delegación de eventos para el formulario del carrito
    document.addEventListener('submit', function(e) {
        if (e.target && e.target.id === 'addToCartForm') {
            e.preventDefault();
            e.stopPropagation();
            
            const formData = new FormData(e.target);

            fetch('/grg/cart/add', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Show success toast
                    const toast = document.createElement('div');
                    toast.className = 'position-fixed top-0 end-0 p-3';
                    toast.style.zIndex = '9999';
                    toast.innerHTML = `
                        <div class="toast show" role="alert">
                            <div class="toast-header bg-success text-white">
                                <i class="bi bi-check-circle me-2"></i>
                                <strong class="me-auto">Éxito</strong>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                            </div>
                            <div class="toast-body">
                                ${data.message || 'Producto agregado al carrito'}
                            </div>
                        </div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);

                    // Update cart count
                    const cartCount = document.getElementById('cartCount');
                    if (cartCount) {
                        cartCount.textContent = data.cart_item_count;
                        cartCount.style.display = data.cart_item_count > 0 ? 'inline-block' : 'none';
                        
                        // Animate cart icon
                        const cartLink = document.getElementById('cartLink');
                        if (cartLink) {
                            cartLink.classList.add('text-success');
                            setTimeout(() => cartLink.classList.remove('text-success'), 1000);
                        }
                    }

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('itemDetailModal'));
                    if (modal) modal.hide();
                } else {
                    alert(data.message || 'Error al agregar producto');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Error de conexión');
            });
            
            return false;
        }
    }, true);
</script>
<?php include __DIR__ . '/../layouts/app.php'; ?>
