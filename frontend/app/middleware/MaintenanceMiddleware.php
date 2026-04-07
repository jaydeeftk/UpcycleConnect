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

    $isAdmin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
    
    if (true && !$isAdmin) { 
        view('maintenance_page');
        exit;
    }

    return $next($request);
}
}