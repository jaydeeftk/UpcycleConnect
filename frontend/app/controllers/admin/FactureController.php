<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class FactureController
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
            $result = $this->api->get('/admin/factures');
            return view('admin.factures.index', ['factures' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.factures.index', ['factures' => [], 'error' => $e->getMessage()]);
        }
    }
}