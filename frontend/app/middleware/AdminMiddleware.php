<?php
namespace App\Middleware;

class AdminMiddleware
{
    public static function handle(): void
    {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['statut'] ?? '') !== 'admin') {
            http_response_code(403);
            ob_start();
            $content = '<div class="min-h-screen flex items-center justify-center"><div class="text-center"><h1 class="text-8xl font-bold text-red-500">403</h1><p class="text-xl mt-4 font-semibold">Accès Non Autorisé</p><p class="text-gray-500 mt-2">Vous n\'avez pas les permissions pour accéder à l\'administration.</p><a href="/UpcycleConnect-PA2526/frontend/public/" class="mt-6 inline-block text-green-600 hover:underline">Retour à l\'accueil</a></div></div>';
            ob_end_clean();
            require __DIR__ . '/../../ressources/views/layouts/main.php';
            exit;
        }
    }
}