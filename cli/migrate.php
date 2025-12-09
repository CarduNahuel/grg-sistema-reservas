<?php
/**
 * Database Migration Script
 * Executes all SQL migration files
 * Usage: php cli/migrate.php
 */

$config = require dirname(__DIR__) . '/bootstrap/app.php';

use App\Services\Database;

try {
    echo "🔄 Starting database migrations...\n\n";
    
    // Initialize database connection
    $db = Database::getInstance($config['database']);
    $pdo = $db->getConnection();
    
    // Read and execute migration file
    $migrationFile = dirname(__DIR__) . '/database/migrations/001_create_tables.sql';
    
    if (!file_exists($migrationFile)) {
        echo "❌ Migration file not found: $migrationFile\n";
        exit(1);
    }
    
    echo "📄 Reading migration file: 001_create_tables.sql\n";
    
    // Read the SQL file
    $sql = file_get_contents($migrationFile);
    
    // Split by semicolon to get individual statements
    $lines = explode("\n", $sql);
    $statement = '';
    $successCount = 0;
    $skipCount = 0;
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Skip empty lines and comments
        if (empty($trimmed) || strpos($trimmed, '--') === 0) {
            continue;
        }
        
        $statement .= $line . "\n";
        
        // Check if statement ends with semicolon
        if (substr($trimmed, -1) === ';') {
            $finalStatement = trim($statement);
            if (!empty($finalStatement)) {
                try {
                    $pdo->exec($finalStatement);
                    $successCount++;
                    echo "  ✓ Executed statement\n";
                } catch (Exception $e) {
                    // Check if it's a "already exists" error (which we can ignore)
                    if (strpos($e->getMessage(), 'already exists') !== false) {
                        echo "  ⚠ Skipped (already exists)\n";
                        $skipCount++;
                    } else {
                        echo "  ✗ Error: " . $e->getMessage() . "\n";
                        throw $e;
                    }
                }
            }
            $statement = '';
        }
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "Migration Results:\n";
    echo "✓ Successful: $successCount\n";
    echo "⚠ Skipped: $skipCount\n";
    echo str_repeat("=", 50) . "\n";
    
    // Now seed the database
    echo "\n🌱 Seeding database with initial data...\n\n";
    
    $seedFile = dirname(__DIR__) . '/database/seeders/001_seed_initial_data.sql';
    if (file_exists($seedFile)) {
        echo "📄 Reading seed file: 001_seed_initial_data.sql\n";
        
        $seedSQL = file_get_contents($seedFile);
        $seedLines = explode("\n", $seedSQL);
        $seedStatement = '';
        $seedCount = 0;
        
        foreach ($seedLines as $line) {
            $trimmed = trim($line);
            
            if (empty($trimmed) || strpos($trimmed, '--') === 0) {
                continue;
            }
            
            $seedStatement .= $line . "\n";
            
            if (substr($trimmed, -1) === ';') {
                $finalStatement = trim($seedStatement);
                if (!empty($finalStatement)) {
                    try {
                        $pdo->exec($finalStatement);
                        $seedCount++;
                        echo "  ✓ Seeded\n";
                    } catch (Exception $e) {
                        echo "  ⚠ " . $e->getMessage() . "\n";
                    }
                }
                $seedStatement = '';
            }
        }
        
        echo "\n✅ Database seeded with $seedCount operations\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✅ All migrations and seeds completed successfully!\n";
    echo str_repeat("=", 50) . "\n";
    echo "\n📝 Next steps:\n";
    echo "1. Access http://localhost/grg/ in your browser\n";
    echo "2. Use test credentials:\n";
    echo "   - Email: cliente1@email.com\n";
    echo "   - Password: password123\n";
    echo "3. Or email: owner1@email.com for restaurant owner account\n\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "\n❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
?>
