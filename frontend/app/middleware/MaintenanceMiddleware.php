<?php
namespace App\Middleware;

use App\Services\ApiService;

class MaintenanceMiddleware {
    
    private $api;

    public function __construct() {
        $this->api = new ApiService();
    }

    public function handle($request, $next) {
    // Récupération de l'URL
    $uri = method_exists($request, 'getUri') ? $request->getUri() : ($_SERVER['REQUEST_URI'] ?? '');

    // --- SÉCURITÉ 1 : NE PAS BLOQUER L'API NI L'ADMIN ---
    // Si on est dans /api ou /admin, on passe directement à la suite sans vérifier la maintenance
    if (str_contains($uri, '/api/') || str_contains($uri, '/admin')) {
        return $next($request);
    }

    try {
        $response = $this->api->get('/admin/parametres/');

        $maintenance = $response['maintenance_mode'] ?? ($response['data']['maintenance_mode'] ?? 'false');

        $isMaintenanceActive = ($maintenance === 'true' || $maintenance === true || $maintenance === 1 || $maintenance === "1");

        $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

        if ($isMaintenanceActive && !$isAdmin) {
            view('maintenance_page');
            exit; 
        }
    } catch (\Exception $e) {
    }

    return $next($request);
}
}