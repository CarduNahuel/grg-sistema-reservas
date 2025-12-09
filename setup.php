<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRG - Database Setup Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 900px; }
        .card { box-shadow: 0 8px 32px rgba(0,0,0,0.1); border: none; margin-bottom: 20px; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .badge-step { font-size: 0.9rem; padding: 0.5rem 0.75rem; }
        .code-block { background: #f5f5f5; padding: 15px; border-radius: 5px; border-left: 4px solid #667eea; font-family: monospace; overflow-x: auto; }
        .alert-info { background: #e7f3ff; border: 1px solid #667eea; color: #004085; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg, #764ba2 0%, #667eea 100%); }
        .step { margin-bottom: 30px; }
        .step-number { display: inline-block; width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 50%; text-align: center; line-height: 40px; font-weight: bold; margin-right: 15px; }
        .sql-content { max-height: 300px; overflow-y: auto; background: #f5f5f5; padding: 10px; border-radius: 5px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mt-5">
            <div class="card-header">
                <h1>🍽️ GRG - Database Setup Assistant</h1>
                <p class="mb-0">Gestor de Reservas Gastronómicas - Database Configuration</p>
            </div>
            <div class="card-body">
                <!-- Problem Alert -->
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>⚠️ Notice:</strong> MySQL 8.0 configuration issue detected. Follow the steps below to complete database setup.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <!-- Step 1 -->
                <div class="step">
                    <h5><span class="step-number">1</span>Open phpMyAdmin</h5>
                    <p>Click the link below or navigate to http://localhost/phpmyadmin</p>
                    <p><a href="http://localhost/phpmyadmin" target="_blank" class="btn btn-primary">
                        🔗 Open phpMyAdmin
                    </a></p>
                    <div class="alert alert-info">
                        <strong>Login details:</strong><br>
                        Username: <code>root</code><br>
                        Password: <code>(leave blank)</code>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="step">
                    <h5><span class="step-number">2</span>Execute Migrations (Create Database)</h5>
                    <p>In phpMyAdmin:</p>
                    <ol>
                        <li>Click the <strong>SQL</strong> tab at the top</li>
                        <li>Click the "Choose File" button and select: <code>database/migrations/001_create_tables.sql</code></li>
                        <li>Or copy-paste the SQL below into the query box</li>
                        <li>Click <strong>Execute</strong></li>
                    </ol>
                    <p><strong>Or paste this SQL:</strong></p>
                    <button class="btn btn-sm btn-outline-secondary mb-2" onclick="copyToClipboard('migration-sql')">
                        📋 Copy SQL
                    </button>
                    <div id="migration-sql" class="sql-content" style="max-height: 200px;">
<?php
$migrationFile = __DIR__ . '/database/migrations/001_create_tables.sql';
if (file_exists($migrationFile)) {
    echo htmlspecialchars(file_get_contents($migrationFile));
} else {
    echo "<!-- Migration file not found -->";
}
?>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="step">
                    <h5><span class="step-number">3</span>Execute Seeders (Load Test Data)</h5>
                    <p>After migrations are complete:</p>
                    <ol>
                        <li>Clear the previous SQL query</li>
                        <li>Click the "Choose File" button and select: <code>database/seeders/001_seed_initial_data.sql</code></li>
                        <li>Or copy-paste the SQL below</li>
                        <li>Click <strong>Execute</strong></li>
                    </ol>
                    <p><strong>Or paste this SQL:</strong></p>
                    <button class="btn btn-sm btn-outline-secondary mb-2" onclick="copyToClipboard('seeder-sql')">
                        📋 Copy SQL
                    </button>
                    <div id="seeder-sql" class="sql-content" style="max-height: 200px;">
<?php
$seederFile = __DIR__ . '/database/seeders/001_seed_initial_data.sql';
if (file_exists($seederFile)) {
    echo htmlspecialchars(file_get_contents($seederFile));
} else {
    echo "<!-- Seeder file not found -->";
}
?>
                    </div>
                </div>

                <!-- Test Login -->
                <div class="card bg-light">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">✅ Test Login Credentials</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td><strong>Superadmin:</strong></td>
                                <td><code>admin@grg.com</code> / <code>password123</code></td>
                            </tr>
                            <tr>
                                <td><strong>Restaurant Owner:</strong></td>
                                <td><code>owner1@restaurant.com</code> / <code>password123</code></td>
                            </tr>
                            <tr>
                                <td><strong>Client:</strong></td>
                                <td><code>cliente1@email.com</code> / <code>password123</code></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="card bg-light mt-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">📌 After Setup</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Access the application:</strong></p>
                        <p><a href="/grg" target="_blank" class="btn btn-success">
                            🚀 Open GRG Application
                        </a></p>
                        <p class="mt-3"><strong>Run tests:</strong></p>
                        <div class="code-block">php vendor/bin/phpunit tests/</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white mt-4">
            <p>GRG MVP - Gestor de Reservas Gastronómicas</p>
            <p><small>Database setup assistant • v1.0</small></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('SQL copied to clipboard!');
            }).catch(err => {
                console.error('Failed to copy:', err);
            });
        }
    </script>
</body>
</html>
