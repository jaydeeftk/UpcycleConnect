<?php
namespace App\Middleware;

class AdminMiddleware
{
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? $_SESSION['user']['statut'] ?? '') !== 'admin') {
            http_response_code(403);
            echo view('errors.403');
            exit;
        }
    }

    public static function check()
    {
        self::handle();
    }
}