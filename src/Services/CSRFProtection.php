<?php

namespace App\Services;

class CSRFProtection
{
    private static $tokenName;

    public static function init()
    {
        $config = require __DIR__ . '/../../config/app.php';
        self::$tokenName = $config['csrf_token_name'];

        if (!isset($_SESSION[self::$tokenName])) {
            self::regenerateToken();
        }
    }

    public static function generateToken()
    {
        if (!isset($_SESSION[self::$tokenName])) {
            self::regenerateToken();
        }
        return $_SESSION[self::$tokenName];
    }

    public static function regenerateToken()
    {
        self::$tokenName = self::$tokenName ?? 'csrf_token';
        $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
    }

    public static function validateToken($token)
    {
        if (!isset($_SESSION[self::$tokenName])) {
            return false;
        }

        return hash_equals($_SESSION[self::$tokenName], $token);
    }

    public static function validateRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[self::$tokenName] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            
            if (!self::validateToken($token)) {
                http_response_code(403);
                die('CSRF token validation failed.');
            }
        }
    }

    public static function getTokenInput()
    {
        $token = self::generateToken();
        $name = self::$tokenName ?? 'csrf_token';
        return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
    }

    public static function getTokenMeta()
    {
        $token = self::generateToken();
        $name = self::$tokenName ?? 'csrf_token';
        return '<meta name="' . $name . '" content="' . $token . '">';
    }
}
