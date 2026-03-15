<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class HomeController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        $stats = [
            'objets_sauves'    => 0,
            'utilisateurs'     => 0,
            'projets_realises' => 0,
            'co2_economise'    => 0,
        ];

        try {
            $result = $this->api->get('/admin/dashboard');
            $data = $result['data'] ?? [];
            $stats['utilisateurs']     = $data['total_utilisateurs'] ?? 0;
            $stats['objets_sauves']    = $data['total_annonces'] ?? 0;
            $stats['projets_realises'] = $data['total_evenements'] ?? 0;
        } catch (\Exception $e) {}

        return view('front.home', [
            'title' => 'UpcycleConnect - Donnez une seconde vie à vos objets',
            'stats' => $stats
        ]);
    }
}