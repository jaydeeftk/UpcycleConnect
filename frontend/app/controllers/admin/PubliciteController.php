<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class PubliciteController
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
            $result = $this->api->get('/admin/publicites');
            $publicites = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $publicites = [];
        }
        return view('admin.publicites.index', ['publicites' => $publicites, 'page_title' => 'Campagnes publicitaires']);
    }

    public function annuler($id)
    {
        try {
            $this->api->post('/admin/publicites/' . $id . '/annuler', []);
            $_SESSION['success'] = t('adm_pub_flash_cancelled', 'Campagne annulée.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/admin/publicites');
    }
}
