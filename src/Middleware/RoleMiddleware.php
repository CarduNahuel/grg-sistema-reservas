<?php

namespace App\Middleware;

use App\Services\AuthService;

class RoleMiddleware
{
    private $authService;
    private $allowedRoles;

    public function __construct($allowedRoles = [])
    {
        $this->authService = new AuthService();
        $this->allowedRoles = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    }

    public function handle()
    {
        if (!$this->authService->check()) {
            header('Location: /auth/login');
            exit;
        }

        $hasRole = false;
        foreach ($this->allowedRoles as $role) {
            if ($this->authService->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (!$hasRole) {
            http_response_code(403);
            echo "403 - Acceso denegado. No tienes permisos para acceder a esta página.";
            exit;
        }

        return true;
    }
}
