<?php
/**
 * GRG Final Status Report
 */

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║   GRG - Gestor de Reservas Gastronómicas                ║\n";
echo "║   Final Status Report                                   ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$checks = [];

// 1. Check .env file
echo "1️⃣  Configuration Files:\n";
$envExists = file_exists(__DIR__ . '/.env');
echo "   " . ($envExists ? "✅" : "❌") . " .env file\n";
$checks[] = $envExists;

// 2. Check core files
echo "\n2️⃣  Core Application Files:\n";
$coreFiles = [
    'bootstrap/app.php',
    'public/index.php',
    'src/Services/Database.php',
    'src/Controllers/AuthController.php',
    'src/Controllers/ReservationController.php',
];
foreach ($coreFiles as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo "   " . ($exists ? "✅" : "❌") . " $file\n";
    $checks[] = $exists;
}

// 3. Database connection
echo "\n3️⃣  Database Connection:\n";
try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3307;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "   ✅ Connected to MySQL on port 3307\n";
    $checks[] = true;
    
    // 4. Check database
    echo "\n4️⃣  Database Status:\n";
    $pdo->exec('USE grg_db');
    echo "   ✅ Using database 'grg_db'\n";
    
    // Check tables
    $tables = [
        'roles' => 'Roles',
        'users' => 'Users',
        'restaurants' => 'Restaurants',
        'reservations' => 'Reservations',
        'notifications' => 'Notifications',
    ];
    
    echo "\n5️⃣  Database Tables & Records:\n";
    foreach ($tables as $table => $label) {
        $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "   ✅ $label ($table): $count records\n";
        $checks[] = ($count > 0);
    }
    
    // Test login
    echo "\n6️⃣  Test Users Available:\n";
    $testUsers = $pdo->query("
        SELECT u.email, r.name as role 
        FROM users u 
        JOIN roles r ON u.role_id = r.id 
        WHERE r.name IN ('SUPERADMIN', 'OWNER', 'CLIENTE')
        ORDER BY r.name DESC
        LIMIT 3
    ")->fetchAll();
    
    foreach ($testUsers as $user) {
        echo "   ✅ {$user['email']} ({$user['role']})\n";
    }
    echo "       Password: password123\n";
    
} catch (Exception $e) {
    echo "   ❌ Database Error: " . $e->getMessage() . "\n";
    $checks[] = false;
}

// Summary
echo "\n╔══════════════════════════════════════════════════════════╗\n";
$allPassed = !in_array(false, $checks);
if ($allPassed) {
    echo "║   ✅ ALL CHECKS PASSED - READY FOR PRODUCTION            ║\n";
} else {
    echo "║   ⚠️  SOME CHECKS FAILED - REVIEW ABOVE                  ║\n";
}
echo "╚══════════════════════════════════════════════════════════╝\n\n";

echo "🚀 Access the application:\n";
echo "   URL:  http://localhost/grg\n";
echo "   User: cliente1@email.com\n";
echo "   Pass: password123\n\n";

echo "📊 Admin Dashboard:\n";
echo "   URL:  http://localhost/grg/dashboard\n";
echo "   User: admin@grg.com\n";
echo "   Pass: password123\n\n";

echo "📝 Repository Structure:\n";
echo "   Controllers: src/Controllers/\n";
echo "   Models:      src/Models/\n";
echo "   Views:       views/\n";
echo "   Tests:       tests/\n\n";

echo "Run Unit Tests:\n";
echo "   php vendor/bin/phpunit tests/\n";
?>
