<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class ServicesController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/services');
            $services = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $services = [];
        }

        return view('admin.services.index', [
            'services' => $services,
            'page_title' => 'Prestations & Services',
            'page_subtitle' => 'Gérez les offres d\'upcycling proposées par les professionnels'
        ]);
    }

    public function store()
    {
        $data = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'prix' => (float)($_POST['prix'] ?? 0),
            'duree' => (int)($_POST['duree'] ?? 0),
            'categorie' => $_POST['categorie'] ?? 'general'
        ];

        try {
            $this->api->post('/admin/services', $data);
        } catch (\Exception $e) {}

        header('Location: /admin/services');
        exit;
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/admin/services/' . $id);
        } catch (\Exception $e) {}

        header('Location: /admin/services');
        exit;
    }
}