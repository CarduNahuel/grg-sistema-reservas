<?php
/**
 * GRG Database Setup via MySQL CLI with Protocol Fixing
 * Attempts multiple connection methods bypassing plugin issues
 */

echo "🔧 GRG Database Setup - Advanced Method\n";
echo "==================================================\n\n";

$mysqlPath = 'C:\\xampp\\mysql\\bin\\mysql.exe';
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'grg_db';
$socket = 'MySQL';

// Check if MySQL executable exists
if (!file_exists($mysqlPath)) {
    echo "❌ MySQL executable not found at: $mysqlPath\n";
    exit(1);
}

echo "✓ MySQL CLI found at: $mysqlPath\n\n";

// Method 1: Try using TCP/IP protocol with port specification
echo "📝 Method 1: Using TCP/IP protocol...\n";
$migrationFile = __DIR__ . '/database/migrations/001_create_tables.sql';
$seederFile = __DIR__ . '/database/seeders/001_seed_initial_data.sql';

// Create temp files with absolute paths in quotes
$migrationContent = file_get_contents($migrationFile);
$seederContent = file_get_contents($seederFile);

// Try with explicit protocol and settings
$cmd = "\"$mysqlPath\" --protocol=tcp -h $host -u $user --port=3306 < \"$migrationFile\" 2>&1";
echo "Running migrations...\n";

$output = array();
$returnCode = 0;
exec($cmd, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Migrations executed successfully!\n";
    if (!empty($output)) {
        foreach ($output as $line) {
            if (trim($line)) echo "   $line\n";
        }
    }
} else {
    echo "❌ Method 1 failed. Return code: $returnCode\n";
    foreach ($output as $line) {
        if (trim($line)) echo "   $line\n";
    }
    
    // Method 2: Try with socket connection
    echo "\n📝 Method 2: Using socket connection...\n";
    $cmd = "\"$mysqlPath\" --socket=$socket -u $user < \"$migrationFile\" 2>&1";
    echo "Running migrations with socket...\n";
    
    $output = array();
    $returnCode = 0;
    exec($cmd, $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✅ Migrations executed via socket!\n";
    } else {
        echo "❌ Socket method also failed.\n";
        foreach ($output as $line) {
            if (trim($line)) echo "   $line\n";
        }
    }
}

// Try seeders regardless
echo "\n📝 Executing seeders...\n";
$cmd = "\"$mysqlPath\" --protocol=tcp -h $host -u $user --port=3306 $database < \"$seederFile\" 2>&1";

$output = array();
$returnCode = 0;
exec($cmd, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Seeders executed successfully!\n";
} else {
    echo "⚠️  Seeders execution note:\n";
    foreach ($output as $line) {
        if (trim($line)) echo "   $line\n";
    }
}

echo "\n==================================================\n";
echo "📋 Alternative Setup Instructions:\n\n";
echo "If automatic setup failed, use phpMyAdmin:\n";
echo "1. Open: http://localhost/phpmyadmin\n";
echo "2. Click 'SQL' tab (top menu)\n";
echo "3. Copy contents of: database/migrations/001_create_tables.sql\n";
echo "4. Paste and click Execute\n";
echo "5. Then repeat for: database/seeders/001_seed_initial_data.sql\n\n";
echo "Or verify database manually:\n";
echo "mysql -h localhost -u root -e \"USE grg_db; SHOW TABLES;\"\n";
echo "==================================================\n";
?>
