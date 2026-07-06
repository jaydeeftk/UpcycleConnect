<?php

if (!function_exists('csrf_token')) {

    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
    }

    function csrf_verify(): bool
    {
        $real = $_SESSION['csrf_token'] ?? '';
        if ($real === '') {
            return false;
        }
        $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return is_string($sent) && $sent !== '' && hash_equals($real, $sent);
    }
}
