<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class EvenementController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();

        if (!isset($_SESSION['user'])) {
            redirect('/UpcycleConnect-PA2526/frontend/public/login');
        }

        if (isset($_SESSION['token'])) {
            $this->api->setToken($_SESSION['token']);
        }
    }

    public function index()
    {
        try {
            $result = $this->api->get('/salarie/evenements');

            return view('salaries.evenements.index', [
                'evenements'    => $result['data'] ?? [],
                'page_title'    => 'Gestion des événements',
                'page_subtitle' => 'Créez et gérez vos événements'
            ]);
        } catch (\Exception $e) {
            return view('salaries.evenements.index', [
                'error'         => $e->getMessage(),
                'evenements'    => [],
                'page_title'    => 'Gestion des événements',
                'page_subtitle' => 'Créez et gérez vos événements'
            ]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/salarie/evenements/create', [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'date'        => $_POST['date'] ?? '',
                'capacite'    => (int)($_POST['capacite'] ?? 0),
                'id_salaries' => $_SESSION['user']['id'] ?? 0
            ]);

            $_SESSION['success'] = 'Événement ajouté avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/UpcycleConnect-PA2526/frontend/public/salaries/evenements');
    }

    public function edit($id)
    {
        try {
            $result = $this->api->get('/salarie/evenements/' . $id);

            return view('salaries.evenements.index', [
                'evenements'     => $this->api->get('/salarie/evenements')['data'] ?? [],
                'evenement_edit' => $result['data'] ?? null,
                'page_title'     => 'Modifier un événement',
                'page_subtitle'  => 'Modifiez les informations de l\'événement'
            ]);
        } catch (\Exception $e) {
            return view('salaries.evenements.index', [
                'error'         => $e->getMessage(),
                'evenements'    => [],
                'page_title'    => 'Gestion des événements',
                'page_subtitle' => 'Créez et gérez vos événements'
            ]);
        }
    }

    public function update($id)
    {
        try {
            $this->api->put('/salarie/evenements/' . $id, [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'date'        => $_POST['date'] ?? '',
                'capacite'    => (int)($_POST['capacite'] ?? 0),
            ]);

            $_SESSION['success'] = 'Événement modifié avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/UpcycleConnect-PA2526/frontend/public/salaries/evenements');
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/salarie/evenements/' . $id);
            $_SESSION['success'] = 'Événement supprimé avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/UpcycleConnect-PA2526/frontend/public/salaries/evenements');
    }
}