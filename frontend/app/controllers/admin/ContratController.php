<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class ContratController
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
        $result = $this->api->get('/admin/contrats');
        $contrats = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
    } catch (\Exception $e) { $contrats = []; }
    return view('admin.contrats.index', ['contrats' => $contrats, 'page_title' => 'Contrats']);
}
}