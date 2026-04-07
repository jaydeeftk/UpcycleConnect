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

        if (str_contains($uri, '/api/') || 
            str_contains($uri, '/admin') || 
            str_contains($uri, '/admin-portal-access') || 
            str_contains($uri, '/login')) {
            return $next($request);
        }

        try {
            $response = $this->api->get('/parametres');

            $maintenance = $response['data']['maintenance_mode'] ?? ($response['data']['site_settings'] ?? 'false');
            
            $isMaintenanceActive = ($maintenance === 'true' || $maintenance === '1' || $maintenance === true);
            $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';


            if ($isMaintenanceActive && !$isAdmin) {
                view('maintenance.index', ['layout' => 'blank']);
                exit; 
            }
        } catch (\Exception $e) {
        }

        return $next($request);
    }
}