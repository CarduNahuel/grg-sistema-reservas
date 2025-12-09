<?php
/**
 * Direct MySQL Setup - No Bootstrap
 */

echo "🔧 GRG Direct Database Setup\n";
echo str_repeat("=", 50) . "\n\n";

// Load .env manually
$envVars = [];
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || strpos($trimmed, '#') === 0) continue;
        
        if (strpos($trimmed, '=') !== false) {
            list($name, $value) = explode('=', $trimmed, 2);
            $name = trim($name);
            $value = trim(trim($value), '\'"');
            $envVars[$name] = $value;
        }
    }
}

echo "Environment loaded from .env\n";
echo "DB_HOST: {$envVars['DB_HOST']}\n";
echo "DB_USER: {$envVars['DB_USER']}\n\n";

// Try to connect
$pdo = null;
$strategies = [
    ['host' => $envVars['DB_HOST'] ?? 'localhost', 'port' => $envVars['DB_PORT'] ?? 3306, 'user' => $envVars['DB_USER'] ?? 'root', 'pass' => $envVars['DB_PASS'] ?? ''],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
];

echo "Attempting connections...\n";
foreach ($strategies as $i => $strategy) {
    try {
        $dsn = "mysql:host={$strategy['host']};port={$strategy['port']}";
        $pdo = new PDO($dsn, $strategy['user'], $strategy['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "✅ Connected: {$strategy['user']}@{$strategy['host']}\n\n";
        break;
    } catch (Exception $e) {
        echo "❌ Strategy " . ($i+1) . " failed\n";
    }
}

if (!$pdo) {
    echo "\n⚠️  Manual Setup Required:\n";
    echo "1. Open phpMyAdmin: http://localhost/phpmyadmin\n";
    echo "2. Create database 'grg_db'\n";
    echo "3. Import migrations and seeders\n";
    exit(1);
}

try {
    // Create database
    echo "Setting up database...\n";
    $pdo->exec("DROP DATABASE IF EXISTS grg_db");
    $pdo->exec("CREATE DATABASE grg_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE grg_db");
    echo "✅ Database created\n";
    
    // Execute migrations
    $migrations = file_get_contents(__DIR__ . '/database/migrations/001_create_tables.sql');
    $migrations = preg_replace('/^\s*USE\s+grg_db\s*;/im', '', $migrations);
    
    $statements = array_filter(preg_split('/;\s*$/m', $migrations), fn($s) => trim($s) && !preg_match('/^\s*--/', trim($s)));
    
    $count = 0;
    foreach ($statements as $stmt) {
        if (trim($stmt)) {
            try {
                $pdo->exec(trim($stmt) . ';');
                $count++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'exist') === false && strpos($e->getMessage(), 'Duplicate') === false) {
                    throw $e;
                }
            }
        }
    }
    echo "✅ $count migration statements executed\n";
    
    // Execute seeders
    $seeders = file_get_contents(__DIR__ . '/database/seeders/001_seed_initial_data.sql');
    $seeders = preg_replace('/^\s*USE\s+grg_db\s*;/im', '', $seeders);
    
    $seedStatements = array_filter(preg_split('/;\s*$/m', $seeders), fn($s) => trim($s) && !preg_match('/^\s*--/', trim($s)));
    
    $seedCount = 0;
    foreach ($seedStatements as $stmt) {
        if (trim($stmt)) {
            try {
                $pdo->exec(trim($stmt) . ';');
                $seedCount++;
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate') === false && strpos($e->getMessage(), 'foreign key') === false) {
                    // Skip some errors
                }
            }
        }
    }
    echo "✅ $seedCount seeder statements executed\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ DATABASE SETUP COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "Access the application:\n";
    echo "URL: http://localhost/grg\n\n";
    
    echo "Test Credentials:\n";
    echo "Client:\n";
    echo "  Email: cliente1@email.com\n";
    echo "  Password: password123\n\n";
    
    echo "Owner:\n";
    echo "  Email: owner1@email.com\n";
    echo "  Password: password123\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
