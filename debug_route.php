<?php
// Archivo de debug para verificar routing
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "=== DEBUG INFO ===\n";
echo "REQUEST URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "REQUEST METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "QUERY STRING: " . $_SERVER['QUERY_STRING'] . "\n";

// Simulemos la conversión de ruta
$url = '/' . trim($_SERVER['REQUEST_URI'], '/');
$url = preg_replace('#^/grg#', '', $url); // Remove /grg prefix

echo "Cleaned URL: " . $url . "\n";

// Test regex conversion
$testPaths = [
    '/admin/orders',
    '/admin/orders/{id}/complete',
    '/admin/orders/{id}/cancel'
];

echo "\n=== REGEX TEST ===\n";
foreach ($testPaths as $path) {
    $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_-]+)', $path);
    $pattern = '#^' . $pattern . '$#';
    echo "Path: $path\n";
    echo "Pattern: $pattern\n";
    echo "Test against '/admin/orders/4/complete': " . (preg_match($pattern, '/admin/orders/4/complete') ? 'MATCH' : 'NO MATCH') . "\n\n";
}

// Check if session exists
echo "=== SESSION INFO ===\n";
session_start();
echo "Session started: " . (isset($_SESSION['user_id']) ? 'YES (ID: ' . $_SESSION['user_id'] . ')' : 'NO') . "\n";

// Check if coming from orders page redirect
echo "\n=== FLASH MESSAGES ===\n";
if (isset($_SESSION['success'])) {
    echo "SUCCESS: " . $_SESSION['success'] . "\n";
}
if (isset($_SESSION['error'])) {
    echo "ERROR: " . $_SESSION['error'] . "\n";
}
?>