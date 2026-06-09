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
        if (!isset($_SESSION['user'])) {
            redirect('/login');
            return;
        }
        $payload = [
            'nom_objet'    => $_POST['nom_objet'] ?? '',
            'categorie'    => $_POST['categorie'] ?? '',
            'type_objet'   => $_POST['type_objet'] ?? '',
            'etat'         => $_POST['etat'] ?? '',
            'description'  => $_POST['description'] ?? '',
            'localisation' => $_POST['localisation'] ?? '',
            'budget'       => $_POST['budget'] ?? '',
        ];
        try {
            $this->api->post('/prestations/demandes', $payload);
            redirect('/mes-prestations?envoye=1');
        } catch (\Exception $e) {
            redirect('/mes-prestations?erreur=1');
        }
    }
}