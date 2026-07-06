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
        $this->api->setToken($_SESSION['user']['token'] ?? '');

        $evenements = [];
        $formations = [];
        $libres     = [];
        $stats      = [];
        $depots     = [];

        try {
            $res        = $this->api->get('/planning/' . $_SESSION['user']['id']);
            $data       = $res['data'] ?? $res;
            $evenements = $data['evenements'] ?? [];
            $formations = $data['formations'] ?? [];
            $libres     = $data['libres']     ?? [];
            $depots     = $data['depots']     ?? [];
            $stats      = $data['stats']      ?? [];
        } catch (\Exception $e) {
            $evenements = [];
            $formations = [];
            $libres     = [];
            $depots     = [];
            $stats      = [];
        }

        return view('front.planning.index', [
            'title'      => 'Mon Planning - UpcycleConnect',
            'evenements' => $evenements,
            'formations' => $formations,
            'depots'     => $depots,
            'libres'     => $libres,
            'stats'      => $stats,
        ]);
    }

    public function ajouter()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        try {
            $this->api->post('/planning/personnel', [
                'titre'       => $_POST['titre'] ?? '',
                'date_debut'  => $_POST['date_debut'] ?? '',
                'date_fin'    => $_POST['date_fin'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]);
            $_SESSION['success'] = 'Entrée ajoutée à votre planning.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/planning');
    }

    public function supprimer($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        try {
            $this->api->delete('/planning/personnel/' . (int)$id);
            $_SESSION['success'] = 'Entrée supprimée.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/planning');
    }
}