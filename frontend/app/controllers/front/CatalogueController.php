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

    public function showService($id)
    {
        $service = [];
        try {
            $res     = $this->api->get('/services/' . $id);
            $service = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $service = [];
        }

        return view('front.services.detail', [
            'title'   => ($service['titre'] ?? 'Service') . ' - UpcycleConnect',
            'service' => $service,
        ]);
    }

    public function showFormation($id)
    {
        $formation = [];
        try {
            $userId    = $_SESSION['user']['id'] ?? 0;
            $res       = $this->api->get('/formations/' . $id . '?user_id=' . $userId);
            $formation = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $formation = [];
        }

        return view('front.formations.detail', [
            'title'     => ($formation['titre'] ?? 'Formation') . ' - UpcycleConnect',
            'formation' => $formation,
        ]);
    }

    public function inscrireFormation($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        try {
            $this->api->post('/formations/' . $id . '/inscrire', [
                'id_utilisateur' => $_SESSION['user']['id'] ?? 0,
            ]);
            redirect('/formations/' . $id . '?success=1');
        } catch (\Exception $e) {
            redirect('/formations/' . $id . '?error=' . urlencode($e->getMessage()));
        }
    }

    public function desinscrireFormation($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        try {
            $this->api->post('/formations/' . $id . '/desinscrire', [
                'id_utilisateur' => $_SESSION['user']['id'] ?? 0,
            ]);
            redirect('/formations/' . $id . '?success_desinscription=1');
        } catch (\Exception $e) {
            redirect('/formations/' . $id . '?error=' . urlencode($e->getMessage()));
        }
    }

    public function services()
    {
        $services = [];
        try {
            $res      = $this->api->get('/services');
            $services = $res['data'] ?? $res;
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
            $res        = $this->api->get('/formations');
            $formations = $res['data'] ?? $res;
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
            $res        = $this->api->get('/evenements');
            $evenements = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $evenements = [];
        }

        return view('front.catalogue.evenements', [
            'title'      => 'Événements - UpcycleConnect',
            'evenements' => $evenements,
        ]);
    }
}