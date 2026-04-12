<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class FormationController
{
    private $api;

    public function __construct()
    {
        \App\Middleware\AdminMiddleware::check();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/formations');
            $formations = isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);
        } catch (\Exception $e) { $formations = []; }

        try {
            $salResult = $this->api->get('/admin/salaries/list');
            $salaries = $salResult['data'] ?? (is_array($salResult) ? $salResult : []);
        } catch (\Exception $e) { $salaries = []; }

        return view('admin.formations.index', [
            'formations' => $formations,
            'salaries'   => $salaries,
            'page_title' => 'Catalogue & Validations',
            'page_subtitle' => 'Gérez les formations et approuvez les demandes des salariés'
        ]);
    }

    public function store()
    {
        $data = [
            'titre'         => $_POST['titre'] ?? '',
            'description'   => $_POST['description'] ?? '',
            'prix'          => (float)($_POST['prix'] ?? 0),
            'duree'         => (int)($_POST['duree'] ?? 0),
            'statut'        => $_POST['statut'] ?? 'en_attente',
            'id_salaries'   => (int)($_POST['id_salaries'] ?? 1),
            'date_formation'=> $_POST['date_formation'] ?? date('Y-m-d H:i:s'),
            'places_total'  => (int)($_POST['places_total'] ?? 20),
            'localisation'  => $_POST['localisation'] ?? 'Siège UpcycleConnect'
        ];

        try {
            $this->api->post('/admin/formations', $data);
        } catch (\Exception $e) {}

        header('Location: /admin/formations');
        exit;
    }

    public function valider($id)
    {
        try { $this->api->put('/admin/formations/' . $id . '/valider', []); } catch (\Exception $e) {}
        header('Location: /admin/formations');
        exit;
    }

    public function rejeter($id)
    {
        try { $this->api->put('/admin/formations/' . $id . '/rejeter', []); } catch (\Exception $e) {}
        header('Location: /admin/formations');
        exit;
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/formations/' . $id); } catch (\Exception $e) {}
        header('Location: /admin/formations');
        exit;
    }
}