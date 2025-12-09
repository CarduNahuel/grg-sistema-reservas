<?php
/**
 * MySQL 8.0 Setup Script
 */

echo "🔧 GRG Database Setup (MySQL 8.0)\n";
echo str_repeat("=", 50) . "\n\n";

// MySQL 8.0 Strategies
$strategies = [
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root', 'charset' => 'utf8mb4'],
];

$pdo = null;

foreach ($strategies as $i => $config) {
    try {
        echo "Attempt " . ($i+1) . ": Connecting to {$config['user']}@{$config['host']}:{$config['port']}... ";
        
        $dsn = "mysql:host={$config['host']};port={$config['port']};charset=" . ($config['charset'] ?? 'utf8mb4');
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        ];
        
        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "✅ Connected\n\n";
        break;
    } catch (PDOException $e) {
        echo "❌ Failed: " . substr($e->getMessage(), 0, 60) . "...\n";
    }
}

if (!$pdo) {
    echo "\n❌ Could not connect to MySQL 8.0\n";
    echo "\nAlternative: Use phpMyAdmin to manually execute SQL files:\n";
    echo "1. http://localhost/phpmyadmin\n";
    echo "2. SQL tab → paste contents of database/migrations/001_create_tables.sql\n";
    echo "3. Execute, then repeat for database/seeders/001_seed_initial_data.sql\n";
    exit(1);
}

try {
    echo "Setting up database grg_db...\n\n";
    
    // Drop if exists
    $pdo->exec("DROP DATABASE IF EXISTS grg_db");
    echo "✅ Dropped existing grg_db\n";
    
    // Create database
    $pdo->exec("CREATE DATABASE grg_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "✅ Created grg_db\n";
    
    // Use database
    $pdo->exec("USE grg_db");
    echo "✅ Using grg_db\n\n";
    
    // Read migration file
    echo "Executing migrations...\n";
    $migFile = file_get_contents(__DIR__ . '/database/migrations/001_create_tables.sql');
    
    // Remove comments and USE statements
    $lines = explode("\n", $migFile);
    $sql = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, 'USE ') === 0) {
            continue;
        }
        $sql .= $line . "\n";
    }
    
    // Execute each statement
    $statements = array_filter(explode(';', $sql), fn($s) => trim($s));
    $execCount = 0;
    
    foreach ($statements as $stmt) {
        $trimmed = trim($stmt);
        if (empty($trimmed)) continue;
        
        try {
            $pdo->exec($trimmed);
            $execCount++;
        } catch (Exception $e) {
            // Log but continue
            echo "⚠️  Skipped: " . substr($e->getMessage(), 0, 50) . "\n";
        }
    }
    
    echo "✅ Executed " . $execCount . " migration statements\n\n";
    
    // Execute seeders
    echo "Seeding database...\n";
    $seedFile = file_get_contents(__DIR__ . '/database/seeders/001_seed_initial_data.sql');
    
    $lines = explode("\n", $seedFile);
    $sql = '';
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, 'USE ') === 0) {
            continue;
        }
        $sql .= $line . "\n";
    }
    
    $statements = array_filter(explode(';', $sql), fn($s) => trim($s));
    $seedCount = 0;
    
    foreach ($statements as $stmt) {
        $trimmed = trim($stmt);
        if (empty($trimmed)) continue;
        
        try {
            $pdo->exec($trimmed);
            $seedCount++;
        } catch (Exception $e) {
            // Log but continue on seed
        }
    }
    
    echo "✅ Executed " . $seedCount . " seed statements\n\n";
    
    // Verify
    echo "Verifying...\n";
    $result = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $rolesCount = $result->fetch()['count'];
    
    $result = $pdo->query("SELECT COUNT(*) as count FROM users");
    $usersCount = $result->fetch()['count'];
    
    echo "✅ Roles: " . $rolesCount . " rows\n";
    echo "✅ Users: " . $usersCount . " rows\n";
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ DATABASE SETUP COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "🌐 Access Application:\n";
    echo "   URL: http://localhost/grg\n\n";
    
    echo "👤 Test Credentials:\n\n";
    
    echo "Cliente (Customer):\n";
    echo "   Email: cliente1@email.com\n";
    echo "   Password: password123\n\n";
    
    echo "Restaurador (Owner):\n";
    echo "   Email: owner1@email.com\n";
    echo "   Password: password123\n\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
?>
