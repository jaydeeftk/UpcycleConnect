<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class ConseilController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        $conseils = [];
        $sujets   = [];

        try {
            $conseils = $this->api->get('/conseils');
        } catch (\Exception $e) {
            $conseils = [];
        }

        try {
            $sujets = $this->api->get('/forum/sujets');
        } catch (\Exception $e) {
            $sujets = [];
        }

        return view('front.conseils.index', [
            'title'    => 'Espace Conseils - UpcycleConnect',
            'conseils' => $conseils,
            'sujets'   => $sujets,
        ]);
    }
}