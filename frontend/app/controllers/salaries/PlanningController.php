<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class PlanningController
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
            $result = $this->api->get('/salaries/planning');

            return view('salaries.planning.index', [
                'items'         => $result['data'] ?? [],
                'page_title'    => 'Planning global',
                'page_subtitle' => 'Gérez les événements, formations et ateliers',
                'token'         => $_SESSION['user']['token'] ?? '',
            ]);
        } catch (\Exception $e) {
            return view('salaries.planning.index', [
                'error'         => $e->getMessage(),
                'items'         => [],
                'page_title'    => 'Planning global',
                'page_subtitle' => 'Gérez les événements, formations et ateliers',
                'token'         => $_SESSION['user']['token'] ?? '',
            ]);
        }
    }

    public function storeEvenement()
    {
        try {
            $this->api->post('/salaries/planning/evenement/create', [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'date'        => $_POST['date'] ?? '',
                'capacite'    => (int)($_POST['capacite'] ?? 0),
                'id_salaries' => $_SESSION['user']['id'] ?? 0
            ]);

            $_SESSION['success'] = t('sal_flash_evenement_added', 'Événement ajouté avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/planning');
    }

    
    public function storeFormation()
    {
        try {
            $this->api->post('/salaries/planning/formation/create', [
                'titre'         => $_POST['titre'] ?? '',
                'description'   => $_POST['description'] ?? '',
                'prix'          => (float)($_POST['prix'] ?? 0),
                'duree'         => (int)($_POST['duree'] ?? 0),
                'date_formation'=> $_POST['date_debut'] ?? '',
                'date_fin'      => $_POST['date_fin'] ?? '',
                'id_salaries'   => $_SESSION['user']['id'] ?? 0
            ]);

            $_SESSION['success'] = t('sal_flash_formation_added', 'Formation ajoutée avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/planning');
    }

 
    public function storeAtelier()
    {
        try {
            $this->api->post('/salaries/planning/atelier/create', [
                'theme'       => $_POST['theme'] ?? '',
                'lieu'        => $_POST['lieu'] ?? '',
                'date'        => $_POST['date'] ?? '',
                'id_salaries' => $_SESSION['user']['id'] ?? 0
            ]);

            $_SESSION['success'] = t('sal_flash_atelier_added', 'Atelier ajouté avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/planning');
    }


    public function deleteEvenement($id)
    {
        try {
            $this->api->delete('/salaries/planning/evenement/' . $id);
            $_SESSION['success'] = t('sal_flash_evenement_deleted', 'Événement supprimé avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/planning');
    }

 
    public function deleteFormation($id)
    {
        try {
            $this->api->delete('/salaries/planning/formation/' . $id);
            $_SESSION['success'] = t('sal_flash_formation_deleted', 'Formation supprimée avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/planning');
    }


    public function deleteAtelier($id)
    {
        try {
            $this->api->delete('/salaries/planning/atelier/' . $id);
            $_SESSION['success'] = t('sal_flash_atelier_deleted', 'Atelier supprimé avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/planning');
    }
}