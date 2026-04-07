<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class PrestationController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        $prestations = [];

        try {
            $prestations = $this->api->get('/services');
        } catch (\Exception $e) {
            $prestations = [];
        }

        return view('front.prestations.index', [
            'title' => 'Prestations - UpcycleConnect',
            'prestations' => $prestations
        ]);
    }

    public function show($id)
    {
        $prestation = [];

        try {
            $prestation = $this->api->get('/services/' . $id);
        } catch (\Exception $e) {
            $prestation = [];
        }

        return view('front.prestations.detail', [
            'title' => 'Détail prestation - UpcycleConnect',
            'prestation' => $prestation
        ]);
    }

    public function create()
    {
        return view('front.demandes.create', [
            'title' => 'Faire une demande - UpcycleConnect'
        ]);
    }

    public function store()
    {
        redirect('/UpcycleConnect-PA2526/frontend/public/mes-demandes');
    }
}