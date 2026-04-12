<?php
namespace App\Controllers\Front;

use App\Services\ApiService;

class SalarieController
{
    private $api;

    public function __construct()
    {
        \App\Middleware\SalarieMiddleware::check();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }

    public function dashboard()
    {
        $profil     = [];
        $formations = [];
        $conseils   = [];

        try {
            $r = $this->api->get('/salaries/profil');
            $profil = isset($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        try {
            $r = $this->api->get('/salaries/formations');
            $formations = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        try {
            $r = $this->api->get('/salaries/conseils');
            $conseils = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        return view('salarie.dashboard', [
            'profil'     => $profil,
            'formations' => $formations,
            'conseils'   => $conseils,
            'stats'      => [
                'formations'    => count($formations),
                'conseils'      => count($conseils),
                'events_semaine' => 0,
            ],
            'page_title' => 'Espace Salarié',
        ]);
    }

    public function createFormation()
    {
        return view('salarie.formations.create', ['page_title' => 'Nouvelle formation']);
    }

    public function createConseil()
    {
        return view('salarie.conseils.create', ['page_title' => 'Nouveau conseil']);
    }

    public function storeFormation()
    {
        try {
            $this->api->post('/salaries/formations', [
                'titre'          => $_POST['titre']          ?? '',
                'description'    => $_POST['description']    ?? '',
                'prix'           => (float)($_POST['prix']   ?? 0),
                'duree'          => (int)($_POST['duree']    ?? 0),
                'date_formation' => $_POST['date_formation'] ?? '',
                'places_total'   => (int)($_POST['places_total'] ?? 0),
                'localisation'   => $_POST['localisation']   ?? '',
                'categorie'      => $_POST['categorie']      ?? '',
            ]);
        } catch (\Exception $e) {}
        header('Location: /salarie');
        exit;
    }

    public function deleteFormation($id)
    {
        try { $this->api->delete('/salaries/formations/' . $id); } catch (\Exception $e) {}
        header('Location: /salarie');
        exit;
    }

    public function storeConseil()
    {
        try {
            $this->api->post('/salaries/conseils', [
                'titre'     => $_POST['titre']     ?? '',
                'contenu'   => $_POST['contenu']   ?? '',
                'categorie' => $_POST['categorie'] ?? '',
                'tags'      => $_POST['tags']      ?? '',
            ]);
        } catch (\Exception $e) {}
        header('Location: /salarie');
        exit;
    }

    public function deleteConseil($id)
    {
        try { $this->api->delete('/salaries/conseils/' . $id); } catch (\Exception $e) {}
        header('Location: /salarie');
        exit;
    }

    public function planning()
    {
        $formations = [];
        $evenements = [];

        try {
            $r = $this->api->get('/salaries/formations');
            $all = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
            foreach ($all as $f) {
                $formations[] = [
                    'titre'      => $f['titre'] ?? '',
                    'date_debut' => $f['date'] ?? '',
                    'lieu'       => $f['localisation'] ?? '',
                    'statut'     => $f['statut'] ?? '',
                ];
            }
        } catch (\Exception $e) {}

        try {
            $r = $this->api->get('/planning/' . ($_SESSION['user']['id'] ?? 0));
            $data = $r['data'] ?? $r;
            $evenements = $data['evenements'] ?? [];
        } catch (\Exception $e) {}

        return view('salarie.planning.index', [
            'formations' => $formations,
            'evenements' => $evenements,
            'page_title' => 'Mon Planning',
        ]);
    }
}
