<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class AbonnementController
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
            $result = $this->api->get('/admin/abonnements');
            $abonnements = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $abonnements = [];
        }
        return view('admin.abonnements.index', ['abonnements' => $abonnements, 'page_title' => 'Abonnements Pro']);
    }
}
