<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class AnnonceController
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
        $result = $this->api->get('/admin/annonces');
        $annonces = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
    } catch (\Exception $e) { $annonces = []; }
    return view('admin.annonces.index', ['annonces' => $annonces, 'page_title' => 'Annonces']);
}
    public function validate($id)
    {
        try { $this->api->get('/admin/annonces/' . $id . '/validate'); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/annonces');
    }
    public function reject($id)
    {
        try { $this->api->get('/admin/annonces/' . $id . '/reject'); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/annonces');
    }
}