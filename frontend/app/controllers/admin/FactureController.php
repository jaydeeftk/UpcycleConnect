<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class FactureController
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
            $result = $this->api->get('/admin/factures');
            $factures = $result['data'] ?? [];
        } catch (\Exception $e) { $factures = []; }
        return view('admin.factures.index', ['factures' => $factures, 'page_title' => 'Factures']);
    }
}