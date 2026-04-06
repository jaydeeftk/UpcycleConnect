<?php
namespace App\Middleware;

class AdminMiddleware
{
    public static function check()
    {
        if (!isset($_SESSION['user']) || !isset($_SESSION['token'])) {
            redirect('/UpcycleConnect-PA2526/frontend/public/login');
        }
        if (($_SESSION['user']['role'] ?? '') !== 'admin') {
            redirect('/UpcycleConnect-PA2526/frontend/public/');
        }
    }
}