<?php

namespace App\Controllers\Admin;

use App\Services\ApiService;

class AnnonceController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/annonces');

            return view('admin.annonces.index', [
                'annonces' => $result['data'] ?? [],
                'page_title' => 'Gestion des annonces'
            ]);
        } catch (\Exception $e) {
            return view('admin.annonces.index', [
                'error' => $e->getMessage(),
                'annonces' => [],
                'page_title' => 'Gestion des annonces'
            ]);
        }
    }

    public function validate($id)
    {
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/annonces');
    }

    public function reject($id)
    {
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/annonces');
    }
}