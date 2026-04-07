<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class ConteneurController
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
            $result = $this->api->get('/admin/conteneurs');
            return view('admin.conteneurs.index', ['conteneurs' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.conteneurs.index', ['conteneurs' => [], 'error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/admin/conteneurs/', [
                'localisation'       => $_POST['localisation'] ?? '',
                'capacite'           => $_POST['capacite'] ?? '',
                'statut'             => $_POST['statut'] ?? 'disponible',
                'id_administrateurs' => (int)($_SESSION['user']['id'] ?? 1),
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/conteneurs');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/conteneurs/' . $id); } catch (\Exception $e) {}
        redirect('/admin/conteneurs');
    }
}