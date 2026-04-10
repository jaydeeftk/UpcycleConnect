<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class EvenementController
{
    private $api;
    public function __construct()
    {
        \App\Middleware\AdminMiddleware::handle();
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
                'titre'          => $_POST['titre'] ?? '',
                'description'    => $_POST['description'] ?? '',
                'lieu'           => $_POST['lieu'] ?? '',
                'date_evenement' => $_POST['date_evenement'] ?? '',
                'id_salarie'     => 1
            ]);
        } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/evenements');
    }
    public function delete($id)
    {
        try { $this->api->delete('/admin/evenements/delete/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/evenements');
    }
}