<?php

// Bootstrap the application
$config = require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\Router;
use App\Services\Database;

// Initialize database connection
try {
    Database::getInstance($config['database']);
} catch (Exception $e) {
    if ($config['app']['debug']) {
        die("Database connection error: " . $e->getMessage());
    } else {
        die("Database connection error. Please try again later.");
    }
}

// Initialize router
$router = new Router();

// Define routes
require_once __DIR__ . '/../routes/web.php';

// Dispatch the request
$url = $_GET['url'] ?? '/';
$router->dispatch($url);
