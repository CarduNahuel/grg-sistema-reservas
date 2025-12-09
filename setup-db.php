<?php
/**
 * Direct Database Setup Script
 * Bypasses connection issues by using raw SQL execution
 */

echo "🔧 GRG Database Setup\n";
echo str_repeat("=", 50) . "\n\n";

// Try to load bootstrap
$bootstrapPath = __DIR__ . '/bootstrap/app.php';
if (!file_exists($bootstrapPath)) {
    echo "❌ Bootstrap file not found\n";
    exit(1);
}

// Load configuration and environment
require_once $bootstrapPath;

// Now try multiple connection strategies
use App\Services\Database;

$strategies = [
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => '', 'desc' => 'root (no password)'],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => '', 'desc' => '127.0.0.1 root (no password)'],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root', 'desc' => 'root with password "root"'],
];

$pdo = null;
$connected = false;

echo "Attempting database connections...\n\n";

foreach ($strategies as $strategy) {
    try {
        echo "Trying: {$strategy['desc']}... ";
        $dsn = "mysql:host={$strategy['host']};port={$strategy['port']}";
        $pdo = new PDO($dsn, $strategy['user'], $strategy['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        echo "✅ SUCCESS\n\n";
        $connected = true;
        break;
    } catch (Exception $e) {
        echo "❌ Failed\n";
    }
}

if (!$connected) {
    echo "\n❌ Could not connect to MySQL with any strategy.\n";
    echo "Manual Setup Required:\n";
    echo "1. Open: http://localhost/phpmyadmin\n";
    echo "2. Navigate to SQL tab\n";
    echo "3. Copy content from: database/migrations/001_create_tables.sql\n";
    echo "4. Paste and execute\n";
    echo "5. Repeat for: database/seeders/001_seed_initial_data.sql\n";
    exit(1);
}

echo "Connected successfully!\n\n";
echo "Creating database: grg_db\n";

try {
    // Create database
    $pdo->exec("DROP DATABASE IF EXISTS grg_db");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS grg_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database created\n\n";
    
    // Use the database
    $pdo->exec("USE grg_db");
    
    // Read migrations file
    $migrationFile = __DIR__ . '/database/migrations/001_create_tables.sql';
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Remove USE statement if present (we already did it)
    $sql = str_replace('USE grg_db;', '', $sql);
    
    // Split statements properly
    $statements = preg_split('/;\s*(?:--|$)/m', $sql);
    
    $count = 0;
    foreach ($statements as $statement) {
        $trimmed = trim($statement);
        if (empty($trimmed) || strpos($trimmed, '--') === 0) {
            continue;
        }
        
        try {
            $pdo->exec($trimmed . ';');
            $count++;
        } catch (Exception $e) {
            // Skip if table already exists
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "⚠️  " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "✅ Executed $count migration statements\n\n";
    
    // Now seed the database
    echo "Seeding initial data...\n";
    $seedFile = __DIR__ . '/database/seeders/001_seed_initial_data.sql';
    if (file_exists($seedFile)) {
        $seedSQL = file_get_contents($seedFile);
        $seedStatements = preg_split('/;\s*(?:--|$)/m', $seedSQL);
        
        $seedCount = 0;
        foreach ($seedStatements as $statement) {
            $trimmed = trim($statement);
            if (empty($trimmed) || strpos($trimmed, '--') === 0) {
                continue;
            }
            
            try {
                $pdo->exec($trimmed . ';');
                $seedCount++;
            } catch (Exception $e) {
                // Skip constraint errors
                if (strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "⚠️  " . $e->getMessage() . "\n";
                }
            }
        }
        
        echo "✅ Executed $seedCount seed statements\n\n";
    }
    
    echo str_repeat("=", 50) . "\n";
    echo "✅ DATABASE SETUP COMPLETE!\n";
    echo str_repeat("=", 50) . "\n\n";
    
    echo "Next steps:\n";
    echo "1. Access: http://localhost/grg/\n";
    echo "2. Login with test credentials:\n";
    echo "   - Email: cliente1@email.com\n";
    echo "   - Password: password123\n\n";
    
    echo "Or login as restaurant owner:\n";
    echo "   - Email: owner1@email.com\n";
    echo "   - Password: password123\n\n";
    
} catch (Exception $e) {
    echo "❌ Error during setup: " . $e->getMessage() . "\n";
    echo "Stack: " . $e->getTraceAsString() . "\n";
    exit(1);
}

exit(0);
?>
