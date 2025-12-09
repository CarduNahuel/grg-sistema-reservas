<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\Validator;

class AuthController extends Controller
{
    private $authService;
    private $validator;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->validator = new Validator();
    }

    public function showLogin()
    {
        $this->view('auth.login', [
            'title' => 'Iniciar Sesión - GRG'
        ]);
    }

    public function login()
    {
        $email = $this->sanitize($this->input('email'));
        $password = $this->input('password');
        $remember = $this->input('remember') === 'on';

        // Validate
        if (!$this->validator->validate($_POST, [
            'email' => 'required|email',
            'password' => 'required'
        ])) {
            $this->setFlash('error', $this->validator->getFirstError());
            return $this->back();
        }

        // Attempt login
        $result = $this->authService->login($email, $password, $remember);

        if ($result['success']) {
            // Check for redirect
            // Use app base path to avoid falling into XAMPP default dashboard
            $base = '/grg';
            $redirect = $_SESSION['redirect_after_login'] ?? ($base . '/dashboard');
            unset($_SESSION['redirect_after_login']);
            
            $this->setFlash('success', '¡Bienvenido de nuevo!');
            return $this->redirect($redirect);
        }

        $this->setFlash('error', $result['message']);
        return $this->back();
    }

    public function showRegister()
    {
        $this->view('auth.register', [
            'title' => 'Registrarse - GRG'
        ]);
    }

    public function register()
    {
        // Validate
        if (!$this->validator->validate($_POST, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required'
        ])) {
            $this->setFlash('error', $this->validator->getFirstError());
            return $this->back();
        }

        // Check password confirmation
        if ($this->input('password') !== $this->input('password_confirmation')) {
            $this->setFlash('error', 'Las contraseñas no coinciden.');
            return $this->back();
        }

        $data = $this->sanitize([
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'password' => $this->input('password')
        ]);

        // Determine role
        $role = $this->input('role') === 'owner' ? 'OWNER' : 'CLIENTE';

        $result = $this->authService->register($data, $role);

        if ($result['success']) {
            $this->setFlash('success', '¡Registro exitoso! Bienvenido a GRG.');
              return $this->redirect('/grg/dashboard');
        }

        $this->setFlash('error', $result['message']);
        return $this->back();
    }

    public function logout()
    {
        $this->authService->logout();
        $this->setFlash('success', 'Has cerrado sesión correctamente.');
        return $this->redirect('/grg');
    }

    public function showProfile()
    {
        $user = $this->authService->user();
        
        $this->view('auth.profile', [
            'title' => 'Mi Perfil - GRG',
            'user' => $user
        ]);
    }

    public function updateProfile()
    {
        $userId = $this->authService->userId();

        if (!$this->validator->validate($_POST, [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'phone' => 'required'
        ])) {
            $this->setFlash('error', $this->validator->getFirstError());
            return $this->back();
        }

        $userModel = new \App\Models\User();
        $userModel->update($userId, $this->sanitize([
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'phone' => $this->input('phone')
        ]));

        $this->setFlash('success', 'Perfil actualizado correctamente.');
        return $this->back();
    }

    public function changePassword()
    {
        if (!$this->validator->validate($_POST, [
            'current_password' => 'required',
            'new_password' => 'required|min:8',
            'new_password_confirmation' => 'required'
        ])) {
            $this->setFlash('error', $this->validator->getFirstError());
            return $this->back();
        }

        if ($this->input('new_password') !== $this->input('new_password_confirmation')) {
            $this->setFlash('error', 'Las contraseñas no coinciden.');
            return $this->back();
        }

        $result = $this->authService->changePassword(
            $this->authService->userId(),
            $this->input('current_password'),
            $this->input('new_password')
        );

        if ($result['success']) {
            $this->setFlash('success', $result['message']);
        } else {
            $this->setFlash('error', $result['message']);
        }

        return $this->back();
    }

    public function showForgotPassword()
    {
        return $this->view('auth/forgot-password');
    }

    public function sendResetLink()
    {
        $email = $this->input('email');

        if (empty($email)) {
            $this->setFlash('error', 'Por favor ingresa tu correo electrónico.');
            return $this->back();
        }

        $user = $this->authService->userModel->findByEmail($email);

        // No revelar si el usuario existe o no por seguridad
        $this->setFlash('success', 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.');

        if ($user) {
            $passwordReset = new \App\Models\PasswordReset();
            $token = $passwordReset->createToken($user['id']);

            // Enviar email con el token
            $this->sendResetEmail($user['email'], $token, $user['name']);
        }

        return redirect_to('/grg/auth/login');
    }

    public function showResetPassword($token)
    {
        $passwordReset = new \App\Models\PasswordReset();
        $reset = $passwordReset->findValidToken($token);

        if (!$reset) {
            $this->setFlash('error', 'El enlace de recuperación es inválido o ha expirado.');
            return redirect_to('/grg/auth/forgot-password');
        }

        return $this->view('auth/reset-password', ['token' => $token]);
    }

    public function resetPassword()
    {
        $token = $this->input('token');
        $password = $this->input('password');
        $passwordConfirmation = $this->input('password_confirmation');

        if (empty($password) || empty($passwordConfirmation)) {
            $this->setFlash('error', 'Por favor completa todos los campos.');
            return $this->back();
        }

        if ($password !== $passwordConfirmation) {
            $this->setFlash('error', 'Las contraseñas no coinciden.');
            return $this->back();
        }

        if (strlen($password) < 8) {
            $this->setFlash('error', 'La contraseña debe tener al menos 8 caracteres.');
            return $this->back();
        }

        $passwordReset = new \App\Models\PasswordReset();
        $reset = $passwordReset->findValidToken($token);

        if (!$reset) {
            $this->setFlash('error', 'El enlace de recuperación es inválido o ha expirado.');
            return redirect_to('/grg/auth/forgot-password');
        }

        // Actualizar contraseña
        $user = $this->authService->userModel;
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $db = \App\Models\Database::getInstance();
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $reset['user_id']]);

        // Marcar token como usado
        $passwordReset->markAsUsed($token);

        $this->setFlash('success', 'Tu contraseña ha sido actualizada exitosamente. Ya puedes iniciar sesión.');
        return redirect_to('/grg/auth/login');
    }

    private function sendResetEmail($email, $token, $name)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['MAIL_USERNAME'];
            $mail->Password = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
            $mail->Port = $_ENV['MAIL_PORT'];
            $mail->CharSet = 'UTF-8';

            // Destinatarios
            $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($email, $name);

            // Contenido
            $resetLink = "http://localhost/grg/auth/reset-password/{$token}";
            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña - GRG';
            $mail->Body = "
                <h2>Recuperación de Contraseña</h2>
                <p>Hola {$name},</p>
                <p>Recibimos una solicitud para restablecer tu contraseña en GRG.</p>
                <p>Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                <p><a href='{$resetLink}' style='background-color: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Restablecer Contraseña</a></p>
                <p>Este enlace expirará en 1 hora.</p>
                <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                <hr>
                <p style='color: #666; font-size: 12px;'>GRG - Gestor de Reservas Gastronómicas</p>
            ";

            $mail->send();
        } catch (\Exception $e) {
            // Log del error pero no revelarlo al usuario
            error_log("Error enviando email de recuperación: {$mail->ErrorInfo}");
        }
    }
}
