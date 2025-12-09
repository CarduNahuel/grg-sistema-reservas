<?php

if (!function_exists('url')) {
    function url($path = '') {
        $base = '/grg';
        $path = ltrim($path, '/');
        return $base . ($path ? '/' . $path : '');
    }
}

if (!function_exists('asset')) {
    function asset($path) {
        $base = '/grg/public';
        $path = ltrim($path, '/');
        return $base . '/' . $path;
    }
}

if (!function_exists('redirect')) {
    function redirect_to($url) {
        header('Location: ' . url($url));
        exit;
    }
}
