<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class DashboardController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();

       if (!isset($_SESSION['user'])) {
    redirect('/UpcycleConnect-PA2526/frontend/public/login');
}

        if (isset($_SESSION['token'])) {
            $this->api->setToken($_SESSION['token']);
        }
    }

    public function index()
    {
        try {
            $conseils    = $this->api->get('/salaries/conseils')['data'] ?? [];
            $planning    = $this->api->get('/salaries/planning')['data'] ?? [];

            $evenements = array_filter($planning, fn($i) => $i['type'] === 'evenement');
            $formations  = array_filter($planning, fn($i) => $i['type'] === 'formation');
            $ateliers    = array_filter($planning, fn($i) => $i['type'] === 'atelier');

            return view('salaries.dashboard', [
                'page_title'       => 'Tableau de bord',
                'page_subtitle'    => 'Bienvenue, ' . htmlspecialchars($_SESSION['user']['prenom'] ?? 'Salarié'),
                'nb_conseils'      => count($conseils),
                'nb_evenements'    => count($evenements),
                'nb_formations'    => count($formations),
                'nb_ateliers'      => count($ateliers),
                'prochains_items'  => array_slice($planning, 0, 5),
            ]);
        } catch (\Exception $e) {
            return view('salaries.dashboard', [
                'page_title'      => 'Tableau de bord',
                'page_subtitle'   => 'Bienvenue',
                'error'           => $e->getMessage(),
                'nb_conseils'     => 0,
                'nb_evenements'   => 0,
                'nb_formations'   => 0,
                'nb_ateliers'     => 0,
                'prochains_items' => [],
            ]);
        }
    }
}