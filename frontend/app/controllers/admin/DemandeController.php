<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class DemandeController
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
            $result = $this->api->get('/admin/demandes');
            $demandes = $result['data'] ?? [];
        } catch (\Exception $e) {
            $demandes = [];
        }
        return view('admin.demandes.index', ['demandes' => $demandes]);
    }

    public function valider($id)
    {
        try { $this->api->put('/admin/demandes/valider/' . $id); } catch (\Exception $e) {}
        redirect('/admin/demandes');
    }

    public function refuser($id)
    {
        try { $this->api->put('/admin/demandes/refuser/' . $id); } catch (\Exception $e) {}
        redirect('/admin/demandes');
    }
}