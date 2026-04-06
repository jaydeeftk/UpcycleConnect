<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class AnnonceController
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
            $result = $this->api->get('/admin/annonces');
            return view('admin.annonces.index', ['annonces' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.annonces.index', ['annonces' => [], 'error' => $e->getMessage()]);
        }
    }

    public function validate($id)
    {
        try { $this->api->put('/admin/annonces/' . $id, ['statut' => 'validé']); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/annonces');
    }

    public function reject($id)
    {
        try { $this->api->put('/admin/annonces/' . $id, ['statut' => 'refusé']); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/annonces');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/annonces/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/annonces');
    }
}