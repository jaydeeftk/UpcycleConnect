<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class AtelierController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();

        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        if (isset($_SESSION['token'])) {
            $this->api->setToken($_SESSION['token']);
        }
    }

    public function index()
    {
        try {
            $result = $this->api->get('/salaries/ateliers');

            return view('salaries.ateliers.index', [
                'ateliers'      => $result['data'] ?? [],
                'page_title'    => 'Gestion des ateliers',
                'page_subtitle' => 'Créez et gérez vos ateliers'
            ]);
        } catch (\Exception $e) {
            return view('salaries.ateliers.index', [
                'error'         => $e->getMessage(),
                'ateliers'      => [],
                'page_title'    => 'Gestion des ateliers',
                'page_subtitle' => 'Créez et gérez vos ateliers'
            ]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/salaries/ateliers/create', [
                'theme'        => $_POST['theme'] ?? '',
                'date_atelier' => $_POST['date_atelier'] ?? '',
                'lieu'         => $_POST['lieu'] ?? '',
                'id_salaries'  => $_SESSION['user']['id'] ?? 0
            ]);

            $_SESSION['success'] = 'Atelier ajouté avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/ateliers');
    }

    public function edit($id)
    {
        try {
            $result = $this->api->get('/salaries/ateliers/' . $id);

            return view('salaries.ateliers.index', [
                'ateliers'     => $this->api->get('/salaries/ateliers')['data'] ?? [],
                'atelier_edit' => $result['data'] ?? null,
                'page_title'   => 'Modifier un atelier',
                'page_subtitle'=> 'Modifiez les informations de l\'atelier'
            ]);
        } catch (\Exception $e) {
            return view('salaries.ateliers.index', [
                'error'         => $e->getMessage(),
                'ateliers'      => [],
                'page_title'    => 'Gestion des ateliers',
                'page_subtitle' => 'Créez et gérez vos ateliers'
            ]);
        }
    }

    public function update($id)
    {
        try {
            $this->api->put('/salaries/ateliers/' . $id, [
                'theme'        => $_POST['theme'] ?? '',
                'date_atelier' => $_POST['date_atelier'] ?? '',
                'lieu'         => $_POST['lieu'] ?? '',
            ]);

            $_SESSION['success'] = 'Atelier modifié avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/ateliers');
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/salaries/ateliers/' . $id);
            $_SESSION['success'] = 'Atelier supprimé avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/ateliers');
    }
}