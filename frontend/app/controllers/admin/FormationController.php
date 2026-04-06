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
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/formations');
            return view('admin.formations.index', ['formations' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.formations.index', ['formations' => [], 'error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/admin/formations/', [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'prix'        => (float)($_POST['prix'] ?? 0),
                'duree'       => (int)($_POST['duree'] ?? 0),
                'statut'      => $_POST['statut'] ?? 'actif',
                'id_salaries' => (int)($_POST['id_salaries'] ?? 1),
            ]);
        } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/formations');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/formations/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/formations');
    }
}