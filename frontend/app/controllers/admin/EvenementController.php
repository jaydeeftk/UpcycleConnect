<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class EvenementController
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
            $result = $this->api->get('/admin/evenements');
            return view('admin.evenements.index', ['evenements' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.evenements.index', ['evenements' => [], 'error' => $e->getMessage()]);
        }
    }

    public function create()
    {
        return view('admin.evenements.create', []);
    }

    public function store()
    {
        try {
            $this->api->post('/admin/evenements/', [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'date'        => $_POST['date'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'capacite'    => (int)($_POST['capacite'] ?? 0),
                'statut'      => $_POST['statut'] ?? 'à venir',
                'id_salaries' => (int)($_POST['id_salaries'] ?? 1),
            ]);
        } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/evenements');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/evenements/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/evenements');
    }
}