<?php
// Debug environment variables

echo "Environment Variables Debug:\n\n";

// Manual .env reading
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    echo "📄 .env file contents (first 15 lines):\n";
    $count = 0;
    foreach ($lines as $line) {
        if ($count >= 15) break;
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        echo "  " . substr($line, 0, 50) . "\n";
        $count++;
    }
    echo "\n";
}

// Check $_ENV
echo "Checking \$_ENV:\n";
echo "  DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET') . "\n";
echo "  DB_USER: " . ($_ENV['DB_USER'] ?? 'NOT SET') . "\n";
echo "  DB_PASS: " . ($_ENV['DB_PASS'] ?? 'NOT SET') . " (length: " . strlen($_ENV['DB_PASS'] ?? '') . ")\n";
echo "  DB_NAME: " . ($_ENV['DB_NAME'] ?? 'NOT SET') . "\n";
echo "  DB_PORT: " . ($_ENV['DB_PORT'] ?? 'NOT SET') . "\n";

// Load config
echo "\n\nLoading bootstrap/app.php...\n";
require_once __DIR__ . '/bootstrap/app.php';

echo "\nDatabase config after bootstrap:\n";
$dbConfig = require __DIR__ . '/config/database.php';
echo "  host: " . $dbConfig['host'] . "\n";
echo "  user: " . $dbConfig['username'] . "\n";
echo "  pass: " . (empty($dbConfig['password']) ? "(empty)" : "***") . "\n";
echo "  database: " . $dbConfig['database'] . "\n";
echo "  port: " . $dbConfig['port'] . "\n";
?>
