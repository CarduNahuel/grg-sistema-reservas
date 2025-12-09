<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login($email, $password, $remember = false)
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Esta cuenta está deshabilitada. Comunícate con un administrador.'];
        }

        // Set session
        $this->setUserSession($user);

        // Handle "remember me"
        if ($remember) {
            $this->setRememberToken($user['id']);
        }

        return ['success' => true, 'user' => $user];
    }

    public function register($data, $roleName = 'CLIENTE')
    {
        try {
            // Check if email already exists
            $existingUser = $this->userModel->findByEmail($data['email']);
            if ($existingUser) {
                return ['success' => false, 'message' => 'El email ya está registrado.'];
            }

            // Create user
            $userId = $this->userModel->createWithRole($data, $roleName);

            // Get created user
            $user = $this->userModel->find($userId);

            // Auto-login
            $this->setUserSession($user);

            return ['success' => true, 'user' => $user];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()];
        }
    }

    public function logout()
    {
        // Clear remember token if exists
        if (isset($_SESSION['user_id'])) {
            $this->clearRememberToken($_SESSION['user_id']);
        }

        // Destroy session
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        session_destroy();
    }

    public function check()
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
    }

    public function user()
    {
        if (!$this->check()) {
            return null;
        }

        return $this->userModel->find($_SESSION['user_id']);
    }

    public function userId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public function hasRole($roleName)
    {
        if (!$this->check()) {
            return false;
        }

        return $this->userModel->hasRole($_SESSION['user_id'], $roleName);
    }

    public function isOwner()
    {
        return $this->hasRole('OWNER');
    }

    public function isAdmin()
    {
        return $this->hasRole('SUPERADMIN') || $this->hasRole('RESTAURANT_ADMIN');
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('SUPERADMIN');
    }

    public function isCliente()
    {
        return $this->hasRole('CLIENTE');
    }

    public function canManageRestaurant($restaurantId)
    {
        if (!$this->check()) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->userModel->canManageRestaurant($_SESSION['user_id'], $restaurantId);
    }

    private function setUserSession($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role_id'];
    }

    private function setRememberToken($userId)
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);

        // Store hashed token in database
        $this->userModel->update($userId, ['remember_token' => $hashedToken]);

        // Set cookie (30 days)
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    }

    private function clearRememberToken($userId)
    {
        $this->userModel->update($userId, ['remember_token' => null]);
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }

    public function checkRememberToken()
    {
        if (isset($_COOKIE['remember_token']) && !$this->check()) {
            $token = $_COOKIE['remember_token'];
            $hashedToken = hash('sha256', $token);

            $user = $this->userModel->first('remember_token = ?', [$hashedToken]);

            if ($user && $user['is_active']) {
                $this->setUserSession($user);
                return true;
            }
        }

        return false;
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        if (!$this->userModel->verifyPassword($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta.'];
        }

        $this->userModel->updatePassword($userId, $newPassword);

        return ['success' => true, 'message' => 'Contraseña actualizada correctamente.'];
    }
}
