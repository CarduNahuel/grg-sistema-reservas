<?php
/**
 * Quick test to verify database setup
 */

// Step 1: Check if grg_db exists
echo "Checking database setup...\n\n";

// Try different connection methods
$attempts = [
    ['localhost', 'root', ''],
    ['localhost', 'root', 'root'],
    ['127.0.0.1', 'root', ''],
    ['127.0.0.1', 'root', 'root'],
];

$connected = false;
$pdo = null;

foreach ($attempts as $creds) {
    list($host, $user, $pass) = $creds;
    try {
        $dsn = "mysql:host=$host;port=3306";
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "✅ Connected: $user@$host\n";
        $connected = true;
        break;
    } catch (Exception $e) {
        echo "❌ Failed: $user@$host - " . $e->getMessage() . "\n";
    }
}

if (!$connected) {
    echo "\n⚠️  Could not connect to any MySQL configuration.\n";
    echo "Trying to create database using grg_db that already exists...\n";
    exit(1);
}

// Try to use grg_db
try {
    $pdo->exec("USE grg_db");
    echo "\n✅ Using database: grg_db\n";
    
    // Check if tables exist
    $result = $pdo->query("SHOW TABLES");
    $tables = $result->fetchAll(PDO::FETCH_COLUMN);
    echo "📊 Found " . count($tables) . " tables\n";
    
    if (count($tables) > 0) {
        echo "Tables: " . implode(", ", $tables) . "\n";
    }
    
} catch (Exception $e) {
    echo "⚠️  Database grg_db not ready: " . $e->getMessage() . "\n";
}

exit(0);
?>
