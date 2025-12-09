<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'GRG',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/grg',
    'env' => $_ENV['APP_ENV'] ?? 'development',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? true, FILTER_VALIDATE_BOOLEAN),
    
    // Session configuration
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 7200),
        'cookie_name' => 'grg_session',
        'cookie_path' => '/',
        'cookie_secure' => false, // Set to true in production with HTTPS
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax'
    ],
    
    // Security
    'csrf_token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token',
    'password_min_length' => (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? 8),
    
    // Business rules
    'reservation_no_show_tolerance' => (int)($_ENV['RESERVATION_NO_SHOW_TOLERANCE'] ?? 15),
    'first_restaurant_free' => filter_var($_ENV['FIRST_RESTAURANT_FREE'] ?? true, FILTER_VALIDATE_BOOLEAN),
    'additional_restaurant_price' => (float)($_ENV['ADDITIONAL_RESTAURANT_PRICE'] ?? 50.00),
    
    // Roles
    'roles' => [
        'SUPERADMIN' => 'SUPERADMIN',
        'OWNER' => 'OWNER',
        'RESTAURANT_ADMIN' => 'RESTAURANT_ADMIN',
        'CLIENTE' => 'CLIENTE'
    ]
];
