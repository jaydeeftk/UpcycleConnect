<?php

namespace App\Controllers\Admin;

use App\Services\ApiService;

class CategorieController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/categories');

            return view('admin.categories.index', [
                'categories' => $result['data'] ?? [],
                'page_title' => 'Gestion des catégories'
            ]);
        } catch (\Exception $e) {
            return view('admin.categories.index', [
                'error' => $e->getMessage(),
                'categories' => [],
                'page_title' => 'Gestion des catégories'
            ]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/admin/categories/create', [
                'nom'         => $_POST['nom'] ?? '',
                'description' => $_POST['description'] ?? '',
                'icone'       => $_POST['icone'] ?? 'fa-tag'
            ]);
        } catch (\Exception $e) {}

        redirect('/UpcycleConnect-PA2526/frontend/public/admin/categories');
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/admin/categories/' . $id);
        } catch (\Exception $e) {}

        redirect('/UpcycleConnect-PA2526/frontend/public/admin/categories');
    }
}