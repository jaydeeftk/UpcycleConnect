<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class PlanningController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        $evenements = [];
        $formations = [];
        $stats      = [];

        try {
            $res        = $this->api->get('/planning/' . $_SESSION['user']['id']);
            $data       = $res['data'] ?? $res;
            $evenements = $data['evenements'] ?? [];
            $formations = $data['formations'] ?? [];
            $stats      = $data['stats']      ?? [];
        } catch (\Exception $e) {
            $evenements = [];
            $formations = [];
            $stats      = [];
        }

        return view('front.planning.index', [
            'title'      => 'Mon Planning - UpcycleConnect',
            'evenements' => $evenements,
            'formations' => $formations,
            'stats'      => $stats,
        ]);
    }
}