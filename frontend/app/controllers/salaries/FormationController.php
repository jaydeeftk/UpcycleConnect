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
           $this->api->post('/salaries/formations', [
    'titre'          => $_POST['titre'] ?? '',
    'description'    => $_POST['description'] ?? '',
    'prix'           => (float)($_POST['prix'] ?? 0),
    'duree'          => (int)($_POST['duree'] ?? 0),
    'dates'          => array_values(array_filter([trim($_POST['date'] ?? '')])),
    'date_fin'       => $_POST['date_fin'] ?? '',
    'places_total'   => (int)($_POST['places_total'] ?? 0),
    'localisation'   => $_POST['lieu'] ?? '',
    'categorie'      => $_POST['categorie'] ?? '',
]);

            $_SESSION['success'] = t('sal_flash_formation_added', 'Formation ajoutée avec succès.');
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
                'titre'          => $_POST['titre'] ?? '',
                'description'    => $_POST['description'] ?? '',
                'prix'           => (float)($_POST['prix'] ?? 0),
                'duree'          => (int)($_POST['duree'] ?? 0),
                'dates'          => array_values(array_filter([trim($_POST['date'] ?? '')])),
                'date_fin'       => $_POST['date_fin'] ?? '',
                'places_total'   => (int)($_POST['places_total'] ?? 0),
                'localisation'   => $_POST['lieu'] ?? '',
                'categorie'      => $_POST['categorie'] ?? '',
            ]);

            $_SESSION['success'] = t('sal_flash_formation_updated', 'Formation modifiée avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/formations');
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/salaries/formations/' . $id);
            $_SESSION['success'] = t('sal_flash_formation_deleted', 'Formation supprimée avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/formations');
    }

    public function etapes($id)
    {
        try {
            $formation = $this->api->get('/salaries/formations')['data'] ?? [];
            $formation = array_values(array_filter($formation, fn($f) => (int)($f['id'] ?? 0) === (int)$id))[0] ?? null;

            if (!$formation) {
                $_SESSION['error'] = t('sal_flash_formation_not_found', 'Formation introuvable.');
                redirect('/salaries/formations');
                return;
            }

            $etapes = $this->api->get('/salaries/formations/' . $id . '/etapes')['data'] ?? [];

            return view('salaries.formations.etapes', [
                'formation'     => $formation,
                'etapes'        => $etapes,
                'page_title'    => 'Étapes de la formation',
                'page_subtitle' => 'Construisez le programme pédagogique',
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            redirect('/salaries/formations');
        }
    }

    public function etapesStore($id)
    {
        try {
            $this->api->post('/salaries/formations/' . $id . '/etapes', [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
                'ordre'       => (int)($_POST['ordre'] ?? 0),
            ]);
            $_SESSION['success'] = t('sal_flash_etape_added', 'Étape ajoutée avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/formations/' . $id . '/etapes');
    }

    public function etapesDelete($id, $etapeId)
    {
        try {
            $this->api->delete('/salaries/formations/' . $id . '/etapes/' . $etapeId);
            $_SESSION['success'] = t('sal_flash_etape_deleted', 'Étape supprimée avec succès.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/salaries/formations/' . $id . '/etapes');
    }
}