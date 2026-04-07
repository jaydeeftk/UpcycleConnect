<?php

namespace App\Controllers\Admin;

use App\Services\ApiService;

class DashboardController
{
    private $api;

    public function __construct()
    {
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
                    'total_utilisateurs' => $stats['total_utilisateurs'] ?? 0,
                    'total_annonces'     => $stats['total_annonces'] ?? 0,
                    'total_evenements'   => $stats['total_evenements'] ?? 0,
                    'total_messages'     => $stats['total_messages'] ?? 0,
                ],
                'prestations'      => $prestations,
                'annonces_pending' => [],
                'recent_users'     => [],
                'revenue_monthly'  => []
            ]);
        } catch (\Exception $e) {
            return view('admin.dashboard', [
                'error' => $e->getMessage(),
                'stats' => [
                    'total_utilisateurs' => 0,
                    'total_annonces'     => 0,
                    'total_evenements'   => 0,
                    'total_messages'     => 0,
                ],
                'prestations'      => [],
                'annonces_pending' => [],
                'recent_users'     => [],
                'revenue_monthly'  => []
            ]);
        }
    }
}