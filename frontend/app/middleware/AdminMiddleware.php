<?php
namespace App\Middleware;

class AdminMiddleware
{
    public static function check()
    {
         error_log(print_r($_SESSION, true));
        if (!isset($_SESSION['user']) || !isset($_SESSION['token'])) {
            redirect('/UpcycleConnect-PA2526/frontend/public/login');
        }
    }
}
