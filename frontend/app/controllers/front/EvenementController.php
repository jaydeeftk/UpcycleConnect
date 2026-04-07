<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class EvenementController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        $evenements = [];

        try {
            $evenements = $this->api->get('/evenements');
        } catch (\Exception $e) {
            $evenements = [];
        }

        return view('front.evenements.index', [
            'title' => 'Événements - UpcycleConnect',
            'evenements' => $evenements
        ]);
    }

    public function show($id)
    {
        $evenement = [];

        try {
            $evenement = $this->api->get('/evenements/' . $id);
        } catch (\Exception $e) {
            $evenement = [];
        }

        return view('front.evenements.detail', [
            'title' => 'Détail événement - UpcycleConnect',
            'evenement' => $evenement
        ]);
    }
}