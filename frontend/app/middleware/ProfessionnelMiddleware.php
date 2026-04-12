<?php
namespace App\Middleware;

class ProfessionnelMiddleware
{
    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            redirect('/login');
            exit();
        }

        $role = $_SESSION['user']['role'] ?? '';
        if ($role !== 'professionnel' && $role !== 'admin') {
            http_response_code(403);
            echo view('errors.403');
            exit();
        }
    }
}
