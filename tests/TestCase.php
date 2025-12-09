<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $db;
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Load environment
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
        
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Load config
        $this->config = [
            'database' => require __DIR__ . '/../config/database.php',
            'app' => require __DIR__ . '/../config/app.php',
        ];
        
        // Initialize database
        \App\Services\Database::getInstance($this->config['database']);
        $this->db = \App\Services\Database::getInstance();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Clean up session
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
        }
    }
}
