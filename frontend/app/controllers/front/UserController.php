<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class UserController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function mesDemandes()
    {
        $demandes = [];

        if (isset($_SESSION['user']['id'])) {
            try {
                $demandes = $this->api->get('/demandes/' . $_SESSION['user']['id']);
            } catch (\Exception $e) {
                $demandes = [];
            }
        }

        return view('front.demandes.index', [
            'title' => 'Mes demandes - UpcycleConnect',
            'demandes' => $demandes
        ]);
    }

    public function mesPrestations()
    {
        return view('front.mes-prestations.index', [
            'title' => 'Mes prestations - UpcycleConnect'
        ]);
    }

    public function paiements()
    {
        $paiements = [];

        if (isset($_SESSION['user']['id'])) {
            try {
                $paiements = $this->api->get('/paiements/' . $_SESSION['user']['id']);
            } catch (\Exception $e) {
                $paiements = [];
            }
        }

        return view('front.paiements.index', [
            'title' => 'Paiements - UpcycleConnect',
            'paiements' => $paiements
        ]);
    }

    public function payer()
    {
        return view('front.paiements.payer', [
            'title' => 'Paiement - UpcycleConnect'
        ]);
    }
}