<?php
namespace App\Middleware;

class AdminMiddleware
{
    public static function check()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/UpcycleConnect-PA2526/frontend/public/login');
        }
    }
}