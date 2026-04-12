<?php
namespace App\Middleware;

class MaintenanceMiddleware
{
    public function handle($request, $next)
    {
        $uri = $_SERVER["REQUEST_URI"] ?? "";

        if (str_contains($uri, "/admin") ||
            str_contains($uri, "/admin-portal-access") ||
            str_contains($uri, "/login")) {
            return $next($request);
        }

        $maintenanceFile = "/tmp/.maintenance";
        $isMaintenanceActive = file_exists($maintenanceFile);
        $isAdmin = isset($_SESSION["user"]["role"]) && $_SESSION["user"]["role"] === "admin";

        if ($isMaintenanceActive && !$isAdmin) {
            view("maintenance.index", ["layout" => "blank"]);
            exit;
        }

        return $next($request);
    }
}