<?php

namespace App\Controllers\Admin;

use App\Services\ApiService;

class EvenementController
{
    private $api;

    public function __construct()
    {
        \App\Middleware\AdminMiddleware::handle();
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/evenements');

            return view('admin.evenements.index', [
                'evenements' => $result['data'] ?? [],
                'page_title' => 'Gestion des événements'
            ]);
        } catch (\Exception $e) {
            return view('admin.evenements.index', [
                'error' => $e->getMessage(),
                'evenements' => [],
                'page_title' => 'Gestion des événements'
            ]);
        }
    }

    public function create()
    {
        return view('admin.evenements.index', [
            'page_title' => 'Créer un événement'
        ]);
    }

    public function store()
    {
        try {
            $this->api->post('/admin/evenements', [
                'titre' => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'lieu' => $_POST['lieu'] ?? '',
                'date_evenement' => $_POST['date_evenement'] ?? '',
                'id_salarie' => 1
            ]);

            redirect('/UpcycleConnect-PA2526/frontend/public/admin/evenements');
        } catch (\Exception $e) {
            redirect('/UpcycleConnect-PA2526/frontend/public/admin/evenements');
        }
    }
}