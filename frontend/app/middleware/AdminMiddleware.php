<?php
namespace App\Middleware;

class AdminMiddleware
{
    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            redirect('/UpcycleConnect-PA2526/frontend/public/login');
            exit();
        }

        if ($_SESSION['user']['role'] !== 'admin') {
            echo view('errors.403'); 
            exit(); 
        }
    }
}