<?php

namespace App\Controllers;

use App\Services\CSRFProtection;

abstract class Controller
{
    protected function view($viewPath, $data = [])
    {
        // Initialize CSRF
        CSRFProtection::init();
        
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $viewPath) . '.php';
        
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("View not found: {$viewPath}");
        }
    }

    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect($url, $statusCode = 302)
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    protected function back()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function setFlash($type, $message)
    {
        $_SESSION['flash'][$type] = $message;
    }

    protected function getFlash($type)
    {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }

    protected function hasFlash($type)
    {
        return isset($_SESSION['flash'][$type]);
    }

    protected function input($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function all()
    {
        return array_merge($_GET, $_POST);
    }

    protected function validate($rules)
    {
        $validator = new \App\Services\Validator();
        return $validator->validate($this->all(), $rules);
    }

    protected function sanitize($data)
    {
        return \App\Services\Validator::sanitize($data);
    }
}
