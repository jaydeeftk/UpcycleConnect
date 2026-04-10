<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class FormationController
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
            $result = $this->api->get('/formations');
            $formations = $result ?? [];
        } catch (\Exception $e) { $formations = []; }
        return view('admin.formations.index', ['formations' => $formations, 'page_title' => 'Formations']);
    }
    public function delete($id)
    {
        try { $this->api->delete('/admin/formations/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/formations');
    }
}