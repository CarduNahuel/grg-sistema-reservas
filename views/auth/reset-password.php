<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - GRG</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-shield-lock-fill text-primary" style="font-size: 3rem;"></i>
                            <h3 class="mt-3">Nueva Contraseña</h3>
                            <p class="text-muted">Ingresa tu nueva contraseña para restablecer tu acceso.</p>
                        </div>

                        <?php if (isset($_SESSION['flash']['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['flash']['success']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['flash']['success']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['flash']['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($_SESSION['flash']['error']) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php unset($_SESSION['flash']['error']); ?>
                        <?php endif; ?>

                        <form action="<?= url('/auth/reset-password') ?>" method="POST" id="resetForm">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Mínimo 8 caracteres" 
                                           required 
                                           autofocus
                                           minlength="8">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength mt-2" id="strengthBar"></div>
                                <small class="text-muted" id="strengthText"></small>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password_confirmation" 
                                           name="password_confirmation" 
                                           placeholder="Repite la contraseña" 
                                           required
                                           minlength="8">
                                    <button class="btn btn-outline-secondary" 
                                            type="button" 
                                            id="toggleConfirmation">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback" id="matchError">
                                    Las contraseñas no coinciden
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="bi bi-check-circle"></i> Restablecer Contraseña
                                </button>
                            </div>
                        </form>

                        <div class="text-center">
                            <a href="<?= url('/auth/login') ?>" class="text-decoration-none">
                                <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3 text-white">
                    <small>&copy; <?= date('Y') ?> GRG - Gestor de Reservas Gastronómicas</small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        document.getElementById('toggleConfirmation').addEventListener('click', function() {
            const confirmInput = document.getElementById('password_confirmation');
            const icon = this.querySelector('i');
            
            if (confirmInput.type === 'password') {
                confirmInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                confirmInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.style.width = (strength * 25) + '%';
            
            if (strength === 0) {
                strengthBar.style.backgroundColor = '';
                strengthText.textContent = '';
            } else if (strength === 1) {
                strengthBar.style.backgroundColor = '#dc3545';
                strengthText.textContent = 'Débil';
            } else if (strength === 2) {
                strengthBar.style.backgroundColor = '#ffc107';
                strengthText.textContent = 'Media';
            } else if (strength === 3) {
                strengthBar.style.backgroundColor = '#0dcaf0';
                strengthText.textContent = 'Buena';
            } else {
                strengthBar.style.backgroundColor = '#198754';
                strengthText.textContent = 'Excelente';
            }
        });

        // Validate password match
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmation = document.getElementById('password_confirmation').value;
            const confirmInput = document.getElementById('password_confirmation');
            const matchError = document.getElementById('matchError');
            
            if (password !== confirmation) {
                e.preventDefault();
                confirmInput.classList.add('is-invalid');
                matchError.style.display = 'block';
            } else {
                confirmInput.classList.remove('is-invalid');
                matchError.style.display = 'none';
            }
        });

        document.getElementById('password_confirmation').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmation = this.value;
            const matchError = document.getElementById('matchError');
            
            if (password === confirmation) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                matchError.style.display = 'none';
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
                matchError.style.display = 'block';
            }
        });
    </script>
</body>
</html>
