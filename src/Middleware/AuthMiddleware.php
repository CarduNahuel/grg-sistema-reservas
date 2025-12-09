<?php

namespace App\Middleware;

use App\Services\AuthService;

class AuthMiddleware
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handle()
    {
        // Check remember token first
        $this->authService->checkRememberToken();

        if (!$this->authService->check()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /grg/auth/login');
            exit;
        }

        return true;
    }
}
