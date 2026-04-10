<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class ConteneurController
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
            $result   = $this->api->get('/admin/conteneurs/demandes');
            $demandes = $result['data'] ?? [];
        } catch (\Exception $e) {
            $demandes = [];
        }

        return view('admin.conteneurs.index', [
            'demandes'   => $demandes,
            'page_title' => 'Dépôts objets',
        ]);
    }

    public function accept($id)
    {
        try { $this->api->post('/admin/conteneurs/demandes/' . $id . '/accept', []); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/conteneurs');
    }

    public function refuse($id)
    {
        try { $this->api->post('/admin/conteneurs/demandes/' . $id . '/refuse', []); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/conteneurs');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/conteneurs/demandes/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/conteneurs');
    }
}