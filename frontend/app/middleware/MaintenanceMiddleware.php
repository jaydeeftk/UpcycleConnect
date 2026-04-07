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

    if (str_contains($uri, '/api/') || str_contains($uri, '/admin')) {
        return $next($request);
    }

    try {
        $response = $this->api->get('/admin/parametres/');

        echo "<pre>RÉPONSE API :<br>";
    var_dump($response);
    echo "</pre>";
    die();

} catch (\Exception $e) {
    echo "ERREUR API : " . $e->getMessage();
    die();
}
        $maintenance = $response['maintenance_mode'] ?? ($response['data']['maintenance_mode'] ?? 'false');
        
        $isMaintenanceActive = ($maintenance === 'true' || $maintenance === true || $maintenance === 1 || $maintenance === "1");

        $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';

        if ($isMaintenanceActive && !$isAdmin) {
            view('maintenance.index');
            exit; 
        }
    } catch (\Exception $e) {
    }

    return $next($request);
}
}