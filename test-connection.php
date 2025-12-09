<?php
echo "Testing config loading\n\n";

// Step 1: Load .env manually  
$envFile = __DIR__ . '/.env';
echo "Step 1: Loading .env\n";
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    $trimmed = trim($line);
    if (empty($trimmed) || strpos($trimmed, '#') === 0) continue;
    
    if (strpos($trimmed, '=') !== false) {
        list($name, $value) = explode('=', $trimmed, 2);
        $name = trim($name);
        $value = trim($value);
        if ((strpos($value, '"') === 0 && substr($value, -1) === '"') ||
            (strpos($value, "'") === 0 && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

echo "  DB_HOST=$_ENV[DB_HOST]\n";
echo "  DB_USER=$_ENV[DB_USER]\n";
echo "  DB_PASS=" . (empty($_ENV['DB_PASS']) ? "(empty)" : $_ENV['DB_PASS']) . "\n";
echo "  DB_NAME=$_ENV[DB_NAME]\n\n";

// Step 2: Load config/database.php
echo "Step 2: Loading config/database.php\n";
$config = require __DIR__ . '/config/database.php';
echo "  host: $config[host]\n";
echo "  port: $config[port]\n";
echo "  database: $config[database]\n";
echo "  username: $config[username]\n";
echo "  password: " . (empty($config['password']) ? "(empty)" : $config['password']) . "\n";

// Step 3: Try to connect
echo "\nStep 3: Attempting connection\n";
try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    echo "  DSN: $dsn\n";
    echo "  User: {$config['username']}\n";
    echo "  Pass: " . (empty($config['password']) ? "(empty)" : "***") . "\n";
    
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    echo "  ✅ Connected successfully!\n";
} catch (Exception $e) {
    echo "  ❌ Connection failed: " . $e->getMessage() . "\n";
}
?>
