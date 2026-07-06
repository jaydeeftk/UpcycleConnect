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

        $categorie = $_GET['categorie'] ?? '';
        $prixMax   = $_GET['prix_max'] ?? '';
        $tri       = $_GET['tri'] ?? 'pertinence';

        if ($categorie !== '') {
            $services = array_values(array_filter($services, fn($s) => strtolower($s['categorie'] ?? '') === strtolower($categorie)));
        }

        if ($prixMax !== '' && is_numeric($prixMax)) {
            $services = array_values(array_filter($services, fn($s) => (float)($s['prix'] ?? 0) <= (float)$prixMax));
        }

        switch ($tri) {
            case 'prix_asc':
                usort($services, fn($a, $b) => (float)($a['prix'] ?? 0) <=> (float)($b['prix'] ?? 0));
                break;
            case 'prix_desc':
                usort($services, fn($a, $b) => (float)($b['prix'] ?? 0) <=> (float)($a['prix'] ?? 0));
                break;
            default:
                break;
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

        $categories = array_values(array_unique(array_filter(array_column($formations, 'categorie'))));

        $categorie = $_GET['categorie'] ?? '';
        $prixMax   = $_GET['prix_max'] ?? '';
        $date      = $_GET['date'] ?? '';
        $places    = $_GET['places'] ?? '';
        $tri       = $_GET['tri'] ?? 'date';

        if ($categorie !== '') {
            $formations = array_values(array_filter($formations, fn($f) => ($f['categorie'] ?? '') === $categorie));
        }

        if ($prixMax !== '' && is_numeric($prixMax)) {
            $formations = array_values(array_filter($formations, fn($f) => (float)($f['prix'] ?? 0) <= (float)$prixMax));
        }

        if ($date !== '') {
            $formations = array_values(array_filter($formations, function ($f) use ($date) {
                $d = $f['date'] ?? '';
                if ($d === '') return false;
                return substr($d, 0, 10) === $date;
            }));
        }

        if ($places !== '' && is_numeric($places)) {
            $formations = array_values(array_filter($formations, fn($f) => (int)($f['places_dispo'] ?? 0) >= (int)$places));
        }

        switch ($tri) {
            case 'prix_asc':
                usort($formations, fn($a, $b) => (float)($a['prix'] ?? 0) <=> (float)($b['prix'] ?? 0));
                break;
            case 'prix_desc':
                usort($formations, fn($a, $b) => (float)($b['prix'] ?? 0) <=> (float)($a['prix'] ?? 0));
                break;
            case 'places':
                usort($formations, fn($a, $b) => (int)($b['places_dispo'] ?? 0) <=> (int)($a['places_dispo'] ?? 0));
                break;
            default:
                usort($formations, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
        }

        return view('front.catalogue.formations', [
            'title'      => 'Formations - UpcycleConnect',
            'formations' => $formations,
            'categories' => $categories,
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

        $type         = $_GET['type'] ?? '';
        $tarif        = $_GET['tarif'] ?? '';
        $date         = $_GET['date'] ?? '';
        $localisation = trim($_GET['localisation'] ?? '');
        $tri          = $_GET['tri'] ?? 'date';

        if ($type !== '') {
            $evenements = array_values(array_filter($evenements, fn($e) => ($e['categorie'] ?? '') === $type));
        }

        if ($tarif === 'gratuit') {
            $evenements = array_values(array_filter($evenements, fn($e) => (float)($e['prix'] ?? 0) == 0));
        } elseif ($tarif === 'payant') {
            $evenements = array_values(array_filter($evenements, fn($e) => (float)($e['prix'] ?? 0) > 0));
        }

        if ($date !== '') {
            $evenements = array_values(array_filter($evenements, function ($e) use ($date) {
                $d = $e['date'] ?? '';
                if ($d === '') return false;
                return substr($d, 0, 10) === $date;
            }));
        }

        if ($localisation !== '') {
            $evenements = array_values(array_filter($evenements, function ($e) use ($localisation) {
                return stripos($e['lieu'] ?? '', $localisation) !== false;
            }));
        }

        switch ($tri) {
            case 'prix_asc':
                usort($evenements, fn($a, $b) => (float)($a['prix'] ?? 0) <=> (float)($b['prix'] ?? 0));
                break;
            case 'popularite':
                usort($evenements, fn($a, $b) => (int)($b['participants'] ?? ($b['capacite'] ?? 0)) <=> (int)($a['participants'] ?? ($a['capacite'] ?? 0)));
                break;
            default:
                usort($evenements, fn($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
        }

        return view('front.catalogue.evenements', [
            'title'      => 'Événements - UpcycleConnect',
            'evenements' => $evenements,
        ]);
    }
}