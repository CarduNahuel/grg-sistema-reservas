<?php

namespace App\Middleware;

use App\Services\AuthService;

class GuestMiddleware
{
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handle()
    {
        if ($this->authService->check()) {
            header('Location: /dashboard');
            exit;
        }

        return true;
    }
}
