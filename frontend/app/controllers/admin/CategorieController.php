<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class CategorieController
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
            $result = $this->api->get('/admin/categories');
            $categories = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) { $categories = []; }
        return view('admin.categories.index', ['categories' => $categories, 'page_title' => 'Catégories']);
    }

    public function store()
    {
        try {
            $this->api->post('/admin/categories', [
                'nom'         => $_POST['nom'] ?? '',
                'description' => $_POST['description'] ?? '',
                'icone'       => $_POST['icone'] ?? 'fa-tag',
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/categories');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/categories/' . $id); } catch (\Exception $e) {}
        redirect('/admin/categories');
    }
}