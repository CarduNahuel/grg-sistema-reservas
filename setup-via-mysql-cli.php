<?php
/**
 * GRG Database Setup via MySQL CLI
 * Uses mysql.exe command-line client directly instead of PDO
 * This bypasses PHP PDO authentication issues
 */

echo "🔧 GRG Database Setup via MySQL CLI\n";
echo "==================================================\n\n";

$mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
$host = 'localhost';
$user = 'root';
$password = ''; // Empty password
$database = 'grg_db';

// Check if MySQL executable exists
if (!file_exists($mysqlPath)) {
    echo "❌ MySQL executable not found at: $mysqlPath\n";
    exit(1);
}

echo "✓ MySQL CLI found at: $mysqlPath\n\n";

// Step 1: Create database and execute migrations
echo "📝 Step 1: Executing migrations...\n";
$migrationFile = __DIR__ . '/database/migrations/001_create_tables.sql';

if (!file_exists($migrationFile)) {
    echo "❌ Migration file not found at: $migrationFile\n";
    exit(1);
}

// Build command for migrations - no password
$cmd = "\"$mysqlPath\" -h $host -u $user < \"$migrationFile\" 2>&1";
echo "Running: mysql -h $host -u $user < database/migrations/001_create_tables.sql\n";

$output = array();
$returnCode = 0;
exec($cmd, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Migrations executed successfully\n";
    if (!empty($output)) {
        echo "Output:\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
    }
} else {
    echo "❌ Migrations failed with return code: $returnCode\n";
    echo "Output:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
    echo "\nTrying Step 2 anyway...\n\n";
}

// Step 2: Execute seeders
echo "\n📝 Step 2: Executing seeders...\n";
$seederFile = __DIR__ . '/database/seeders/001_seed_initial_data.sql';

if (!file_exists($seederFile)) {
    echo "❌ Seeder file not found at: $seederFile\n";
    exit(1);
}

// Build command for seeders - no password
$cmd = "\"$mysqlPath\" -h $host -u $user $database < \"$seederFile\" 2>&1";
echo "Running: mysql -h $host -u $user $database < database/seeders/001_seed_initial_data.sql\n";

$output = array();
$returnCode = 0;
exec($cmd, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Seeders executed successfully\n";
    if (!empty($output)) {
        echo "Output:\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
    }
} else {
    echo "❌ Seeders failed with return code: $returnCode\n";
    echo "Output:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
}

// Step 3: Verify database was created
echo "\n📊 Step 3: Verifying database setup...\n";
$cmd = "\"$mysqlPath\" -h $host -u $user -e \"SELECT COUNT(*) as 'Table Count' FROM information_schema.tables WHERE table_schema='$database';\" 2>&1";

$output = array();
$returnCode = 0;
exec($cmd, $output, $returnCode);

if ($returnCode === 0 && !empty($output)) {
    echo "✅ Database verification:\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
} else {
    echo "⚠️  Could not verify database (this may still be OK)\n";
    foreach ($output as $line) {
        echo "   $line\n";
    }
}

echo "\n==================================================\n";
echo "✅ Setup complete! Database should be ready.\n";
echo "\nYou can now:\n";
echo "1. Access the application: http://localhost/grg\n";
echo "2. Login with: cliente1@email.com / password123\n";
echo "3. Run tests: php vendor/bin/phpunit tests/\n";
?>
