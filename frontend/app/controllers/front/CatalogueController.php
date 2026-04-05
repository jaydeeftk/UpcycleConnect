<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class CatalogueController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function services()
    {
        $services = [];
        try {
            $services = $this->api->get('/services');
        } catch (\Exception $e) {
            $services = [];
        }

        return view('front.catalogue.services', [
            'title'    => 'Services - UpcycleConnect',
            'services' => $services,
        ]);
    }

    public function formations()
    {
        $formations = [];
        try {
            $formations = $this->api->get('/formations');
        } catch (\Exception $e) {
            $formations = [];
        }

        return view('front.catalogue.formations', [
            'title'      => 'Formations - UpcycleConnect',
            'formations' => $formations,
        ]);
    }

    public function evenements()
    {
        $evenements = [];
        try {
            $evenements = $this->api->get('/evenements');
        } catch (\Exception $e) {
            $evenements = [];
        }

        return view('front.catalogue.evenements', [
            'title'      => 'Événements - UpcycleConnect',
            'evenements' => $evenements,
        ]);
    }
}