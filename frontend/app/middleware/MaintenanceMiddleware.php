<?php
namespace App\Middleware;

use App\Services\ApiService;

class MaintenanceMiddleware {
    
    private $api;

    public function __construct() {
        $this->api = new ApiService();
    }

    public function handle($request, $next) {
        try {
            $response = $this->api->get('/admin/parametres');
            $maintenance = $response['data']['maintenance_mode'] ?? 'false';

            $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

            if ($maintenance === 'true' && !$isAdmin) {
                return view('maintenance_page');
            }
        } catch (\Exception $e) {
        }

        return $next($request);
    }
}