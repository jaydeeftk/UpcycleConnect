<?php

namespace App\Controllers\Admin;

use App\Services\ApiService;

class ParametreController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $parametres = $this->api->get('/admin/parametres');
            
            return view('admin.parametres.index', [
                'parametres' => $parametres['data'] ?? [],
                'page_title' => 'Paramètres'
            ]);
        } catch (\Exception $e) {
            return view('admin.parametres.index', [
                'error' => $e->getMessage(),
                'parametres' => [],
                'page_title' => 'Paramètres'
            ]);
        }
    }
}