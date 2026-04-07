<?php
namespace App\Middleware;

use App\Services\ApiService;

class MaintenanceMiddleware {
    
    private $api;

    public function __construct() {
        $this->api = new ApiService();
    }

    public function handle($request, $next) {
        $uri = method_exists($request, 'getUri') ? $request->getUri() : ($_SERVER['REQUEST_URI'] ?? '');

        if (str_contains($uri, '/api/')) {
            return $next($request);
        }

        try {
            $response = $this->api->get('/admin/parametres/');

            $maintenance = $response['maintenance_mode'] ?? ($response['data']['maintenance_mode'] ?? 'false');

            $isMaintenanceActive = ($maintenance === 'true' || $maintenance === true || $maintenance === 1 || $maintenance === "1");

            $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

            if ($isMaintenanceActive && !$isAdmin) {
                return view('maintenance_page');
            }
        } catch (\Exception $e) {
        }

        return $next($request);
    }
}