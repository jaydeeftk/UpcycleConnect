<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class ConseilController
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
            $result   = $this->api->get('/admin/conseils');
            $conseils = isset($result['data']) && is_array($result['data'])
                ? $result['data']
                : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) { $conseils = []; }
        return view('admin.conseils.index', [
            'conseils'      => $conseils,
            'page_title'    => 'Modération des Conseils',
            'page_subtitle' => 'Validez ou rejetez les conseils rédigés par les salariés',
        ]);
    }
    public function valider($id)
    {
        try { $this->api->post('/admin/conseils/' . $id . '/valider', []); } catch (\Exception $e) {}
        header('Location: /admin/conseils'); exit;
    }
    public function rejeter($id)
    {
        try { $this->api->post('/admin/conseils/' . $id . '/rejeter', []); } catch (\Exception $e) {}
        header('Location: /admin/conseils'); exit;
    }
    public function delete($id)
    {
        try { $this->api->delete('/admin/conseils/' . $id); } catch (\Exception $e) {}
        header('Location: /admin/conseils'); exit;
    }
}