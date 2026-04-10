<?php
namespace App\Middleware;

class AdminMiddleware
{

    public function __construct()
{
    \App\Middleware\AdminMiddleware::handle();
    $this->api = new ApiService();
    $this->api->setToken($_SESSION['user']['token'] ?? '');
}
    public static function handle(): void
    {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['statut'] ?? '') !== 'admin') {
            http_response_code(403);
            require __DIR__ . '/../../ressources/views/errors/403.php';
            exit;
        }
    }
}