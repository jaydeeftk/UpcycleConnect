<?php
namespace App\Controllers\Front;

use App\Services\ApiService;

class ProfessionnelController
{
    private $api;

    public function __construct()
    {
        \App\Middleware\ProfessionnelMiddleware::check();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }

    public function dashboard()
    {
        $profil   = [];
        $projets  = [];
        $favoris  = [];
        $contrats = [];

        try {
            $r = $this->api->get('/professionnels/profil');
            $profil = isset($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        try {
            $r = $this->api->get('/professionnels/projets');
            $projets = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        try {
            $r = $this->api->get('/professionnels/favoris');
            $favoris = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        try {
            $r = $this->api->get('/professionnels/contrats');
            $contrats = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        return view('professionnel.dashboard', [
            'profil'   => $profil,
            'projets'  => $projets,
            'favoris'  => $favoris,
            'contrats' => $contrats,
            'page_title' => 'Espace Professionnel',
        ]);
    }

    public function createProjet()
    {
        return view('professionnel.projets.create', [
            'page_title' => 'Nouveau projet',
        ]);
    }

    public function storeProjet()
    {
        try {
            $this->api->post('/professionnels/projets', [
                'titre'       => $_POST['titre']       ?? '',
                'description' => $_POST['description'] ?? '',
                'date_debut'  => $_POST['date_debut']  ?? '',
                'statut'      => $_POST['statut']      ?? 'en_cours',
            ]);
        } catch (\Exception $e) {}
        header('Location: /professionnel');
        exit;
    }

    public function deleteProjet($id)
    {
        try { $this->api->delete('/professionnels/projets/' . $id); } catch (\Exception $e) {}
        header('Location: /professionnel');
        exit;
    }

    public function removeFavori($id)
    {
        try { $this->api->delete('/professionnels/favoris/' . $id); } catch (\Exception $e) {}
        header('Location: /professionnel');
        exit;
    }
}
