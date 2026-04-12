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
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/evenements');
            $evenements = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) { $evenements = []; }
        return view('admin.evenements.index', ['evenements' => $evenements, 'page_title' => 'Événements']);
    }

    public function create()
    {
        return view('admin.evenements.create', ['page_title' => 'Créer un événement']);
    }

    public function store()
    {
        try {
            $this->api->post('/admin/evenements', [
                'titre'         => $_POST['titre'] ?? '',
                'description'   => $_POST['description'] ?? '',
                'lieu'          => $_POST['lieu'] ?? '',
                'date'          => $_POST['date_evenement'] ?? $_POST['date'] ?? '',
                'capacite'      => (int)($_POST['capacite'] ?? 50),
                'statut'        => $_POST['statut'] ?? 'à venir',
                'prix'          => (float)($_POST['prix'] ?? 0),
                'id_salaries'   => !empty($_POST['id_salaries']) ? (int)$_POST['id_salaries'] : null,
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/evenements');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/evenements/' . $id); } catch (\Exception $e) {}
        redirect('/admin/evenements');
    }
}