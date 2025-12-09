<?php
// Simple .env parser test

$envFile = __DIR__ . '/.env';
echo "Testing .env parsing\n";
echo "File path: $envFile\n";
echo "File exists: " . (file_exists($envFile) ? "YES" : "NO") . "\n\n";

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
echo "Total lines: " . count($lines) . "\n\n";

$env = [];
foreach ($lines as $index => $line) {
    $trimmed = trim($line);
    
    // Skip empty lines and comments
    if (empty($trimmed) || strpos($trimmed, '#') === 0) {
        continue;
    }
    
    // Parse the line
    if (strpos($trimmed, '=') !== false) {
        list($name, $value) = explode('=', $trimmed, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if ((strpos($value, '"') === 0 && substr($value, -1) === '"') ||
            (strpos($value, "'") === 0 && substr($value, -1) === "'")) {
            $value = substr($value, 1, -1);
        }
        
        $env[$name] = $value;
        $_ENV[$name] = $value;
        putenv("$name=$value");
        
        echo "Line $index: $name = " . (empty($value) ? "(empty)" : $value) . "\n";
    }
}

echo "\n\nFinal results:\n";
echo "DB_HOST: " . ($env['DB_HOST'] ?? 'MISSING') . "\n";
echo "DB_USER: " . ($env['DB_USER'] ?? 'MISSING') . "\n";
echo "DB_PASS: " . (empty($env['DB_PASS']) ? "(empty)" : $env['DB_PASS']) . "\n";
echo "DB_NAME: " . ($env['DB_NAME'] ?? 'MISSING') . "\n";

echo "\nIn \$_ENV:\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'MISSING') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'MISSING') . "\n";
echo "DB_PASS: " . (empty($_ENV['DB_PASS']) ? "(empty)" : $_ENV['DB_PASS']) . "\n";

echo "\nWith getenv():\n";
echo "DB_HOST: " . (getenv('DB_HOST') ?: 'MISSING') . "\n";
echo "DB_USER: " . (getenv('DB_USER') ?: 'MISSING') . "\n";
echo "DB_PASS: " . (getenv('DB_PASS') ?: '(empty)') . "\n";
?>
