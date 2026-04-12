<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class DashboardController
{
    private $api;
    public function __construct()
    {
        \App\Middleware\AdminMiddleware::handle();
        \App\Middleware\AdminMiddleware::check();
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/dashboard');
            $stats = $result['data'] ?? [];
            $prestations = [];
            $visites = ['today' => 0, 'week' => 0, 'month' => 0, 'total' => 0, 'par_jour' => []];
            try {
                $p = $this->api->get('/services');
                $prestations = $p['data'] ?? $p ?? [];
            } catch (\Exception $e) {}
            try {
                $v = $this->api->get('/admin/visites');
                $visites = $v['data'] ?? $v ?? $visites;
            } catch (\Exception $e) {}
            return view('admin.dashboard', [
                'stats' => [
                    'total_utilisateurs' => $stats['utilisateurs'] ?? 0,
                    'total_annonces'     => $stats['annonces'] ?? 0,
                    'total_evenements'   => $stats['evenements'] ?? 0,
                    'total_messages'     => $stats['messages'] ?? 0,
                    'total_formations'   => $stats['formations'] ?? 0,
                    'total_conteneurs'   => $stats['conteneurs'] ?? 0,
                ],
                'visites'          => $visites,
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