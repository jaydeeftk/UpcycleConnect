<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class DashboardController
{
    private $api;
    public function __construct()
    {
        \App\Middleware\AdminMiddleware::check();
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/dashboard');
            $stats = $result['data'] ?? [];
            $prestations = [];
            try {
                $p = $this->api->get('/services');
                $prestations = $p['data'] ?? $p ?? [];
            } catch (\Exception $e) {}
            return view('admin.dashboard', [
                'stats' => [
                    'total_utilisateurs' => $stats['utilisateurs'] ?? 0,
                    'total_annonces'     => $stats['annonces'] ?? 0,
                    'total_evenements'   => $stats['evenements'] ?? 0,
                    'total_messages'     => $stats['messages'] ?? 0,
                ],
                'prestations'      => $prestations,
                'annonces_pending' => [],
                'recent_users'     => [],
                'revenue_monthly'  => []
            ]);
        } catch (\Exception $e) {
            return view('admin.dashboard', [
                'error' => $e->getMessage(),
                'stats' => ['total_utilisateurs'=>0,'total_annonces'=>0,'total_evenements'=>0,'total_messages'=>0],
                'prestations'=>[],'annonces_pending'=>[],'recent_users'=>[],'revenue_monthly'=>[]
            ]);
        }
    }
}