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
        // if (!isset($_SESSION['user'])) {
        //     redirect('/UpcycleConnect-PA2526/frontend/public/login');
        // }

        $evenements = [];
        $stats      = [];

        try {
            $userId     = $_SESSION['user']['id'] ?? null;
            if ($userId) {
                $data       = $this->api->get('/planning/' . $userId);
                $evenements = $data['evenements'] ?? [];
                $stats      = $data['stats']      ?? [];
            }
        } catch (\Exception $e) {
            $evenements = [];
            $stats      = [];
        }

        return view('front.planning.index', [
            'title'      => 'Mon Planning - UpcycleConnect',
            'evenements' => $evenements,
            'stats'      => $stats,
        ]);
    }
}