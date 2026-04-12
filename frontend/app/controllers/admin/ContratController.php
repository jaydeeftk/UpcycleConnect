<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class ContratController
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
            $result = $this->api->get('/admin/contrats');
            $contrats = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) { $contrats = []; }
        return view('admin.contrats.index', ['contrats' => $contrats, 'page_title' => 'Contrats']);
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/contrats/' . $id); } catch (\Exception $e) {}
        redirect('/admin/contrats');
    }

    public function store()
    {
        try {
            $this->api->post('/admin/contrats', [
                'type'           => $_POST['type'] ?? '',
                'date_signature' => $_POST['date_signature'] ?? date('Y-m-d'),
                'date_debut'     => $_POST['date_debut'] ?? '',
                'date_fin'       => $_POST['date_fin'] ?? '',
                'id_professionnels' => (int)($_POST['id_professionnels'] ?? 0),
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/contrats');
    }
}