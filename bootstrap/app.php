<?php

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load helper functions
require_once __DIR__ . '/../src/helpers.php';

// Load environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
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
            
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

// Load configuration files
$config = [
    'app' => require __DIR__ . '/../config/app.php',
    'database' => require __DIR__ . '/../config/database.php',
    'mail' => require __DIR__ . '/../config/mail.php',
];

// Configure session
if (session_status() === PHP_SESSION_NONE) {
    $sessionConfig = $config['app']['session'];
    
    ini_set('session.cookie_lifetime', $sessionConfig['lifetime']);
    ini_set('session.cookie_httponly', $sessionConfig['cookie_httponly']);
    ini_set('session.cookie_samesite', $sessionConfig['cookie_samesite']);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_name($sessionConfig['cookie_name']);
    session_start();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Initialize CSRF protection (sets token name and ensures a token exists)
\App\Services\CSRFProtection::init();

// Set timezone
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Error reporting
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

return $config;
