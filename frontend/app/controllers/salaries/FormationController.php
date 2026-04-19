<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class FormationController
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
            $result = $this->api->get('/salaries/formations');

            return view('salaries.formations.index', [
                'formations'    => $result['data'] ?? [],
                'page_title'    => 'Gestion des formations',
                'page_subtitle' => 'Créez et gérez vos formations'
            ]);
        } catch (\Exception $e) {
            return view('salaries.formations.index', [
                'error'         => $e->getMessage(),
                'formations'    => [],
                'page_title'    => 'Gestion des formations',
                'page_subtitle' => 'Créez et gérez vos formations'
            ]);
        }
    }

    public function store()
    {
        try {
           $this->api->post('/salaries/formations/create', [
    'titre'       => $_POST['titre'] ?? '',
    'description' => $_POST['description'] ?? '',
    'prix'        => (float)($_POST['prix'] ?? 0),
    'duree'       => (int)($_POST['duree'] ?? 0),
    'date'        => $_POST['date'] ?? '',
    'lieu'        => $_POST['lieu'] ?? '',
    'id_salaries' => $_SESSION['user']['id'] ?? 0
]);

            $_SESSION['success'] = 'Formation ajoutée avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/formations');
    }

    public function edit($id)
    {
        try {
            $result = $this->api->get('/salaries/formations/' . $id);

            return view('salaries.formations.index', [
                'formations'     => $this->api->get('/salaries/formations')['data'] ?? [],
                'formation_edit' => $result['data'] ?? null,
                'page_title'     => 'Modifier une formation',
                'page_subtitle'  => 'Modifiez les informations de la formation'
            ]);
        } catch (\Exception $e) {
            return view('salaries.formations.index', [
                'error'         => $e->getMessage(),
                'formations'    => [],
                'page_title'    => 'Gestion des formations',
                'page_subtitle' => 'Créez et gérez vos formations'
            ]);
        }
    }

    public function update($id)
    {
        try {
            $this->api->put('/salaries/formations/' . $id, [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'prix'        => (float)($_POST['prix'] ?? 0),
                'duree'       => (int)($_POST['duree'] ?? 0),
            ]);

            $_SESSION['success'] = 'Formation modifiée avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/formations');
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/salaries/formations/' . $id);
            $_SESSION['success'] = 'Formation supprimée avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/formations');
    }
}