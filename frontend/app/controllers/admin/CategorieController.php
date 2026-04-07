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
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/categories');
            return view('admin.categories.index', ['categories' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.categories.index', ['categories' => [], 'error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/admin/categories', [
                'description'  => $_POST['description'] ?? '',
                'illustration' => $_POST['illustration'] ?? '',
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