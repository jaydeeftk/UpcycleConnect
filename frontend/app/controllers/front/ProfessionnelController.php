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
        $notifications = [];
        $notifsNonLues = 0;

        try {
            $r = $this->api->get('/professionnels/profil');
            $profil = isset($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        try {
            $r = $this->api->get('/notifications');
            $bloc = $r['data'] ?? $r;
            $notifications = $bloc['notifications'] ?? [];
            $notifsNonLues = (int)($bloc['non_lues'] ?? 0);
        } catch (\Exception $e) {}

        $impact = [];
        try {
            $r = $this->api->get('/professionnels/impact');
            $impact = $r['data'] ?? (is_array($r) && !isset($r['success']) ? $r : []);
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
            'notifications' => $notifications,
            'notifsNonLues' => $notifsNonLues,
            'impact'   => $impact,
            'page_title' => 'Espace Professionnel',
            'layout' => 'raw',
        ]);
    }

    public function impactPdf()
    {
        $res = $this->api->getRaw('/professionnels/impact/pdf');
        if (($res['code'] ?? 0) >= 400 || empty($res['body'])) {
            http_response_code($res['code'] ?: 502);
            echo 'Impossible de générer le bilan PDF';
            return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="bilan-impact.pdf"');
        echo $res['body'];
    }

    public function createProjet()
    {
        return view('professionnel.projets.create', [
            'page_title' => 'Nouveau projet',
            'layout' => 'raw',
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

    public function suspendreProjet($id) { $this->transitionProjet($id, 'suspendre'); }
    public function reprendreProjet($id) { $this->transitionProjet($id, 'reprendre'); }
    public function terminerProjet($id)  { $this->transitionProjet($id, 'terminer'); }
    public function rouvrirProjet($id)   { $this->transitionProjet($id, 'rouvrir'); }

    private function transitionProjet($id, $action)
    {
        try { $this->api->post('/professionnels/projets/' . $id . '/' . $action, []); } catch (\Exception $e) {}
        header('Location: /professionnel');
        exit;
    }

    public function resilierContrat($id)
    {
        try { $this->api->post('/professionnels/contrats/' . $id . '/resilier', []); } catch (\Exception $e) {}
        header('Location: /professionnel');
        exit;
    }

    public function notificationLue($id)
    {
        try { $this->api->post('/notifications/' . $id . '/lu', []); } catch (\Exception $e) {}
        header('Location: /professionnel');
        exit;
    }

    public function removeFavori($id)
    {
        try { $this->api->delete('/professionnels/favoris/' . $id); } catch (\Exception $e) {}
        header('Location: /professionnel');
        exit;
    }

    public function recuperation()
    {
        $catalogue = [];
        $reservations = [];
        try {
            $r = $this->api->get('/professionnels/objets');
            $catalogue = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}
        try {
            $r = $this->api->get('/professionnels/objets', ['filtre' => 'mes-reservations']);
            $reservations = isset($r['data']) && is_array($r['data']) ? $r['data'] : (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        return view('professionnel.recuperation.index', [
            'catalogue'    => $catalogue,
            'reservations' => $reservations,
            'page_title'   => 'Récupération',
            'layout' => 'raw',
        ]);
    }

    public function reserverObjet($id)  { $this->actionObjet($id, 'reserver'); }
    public function recupererObjet($id) { $this->actionObjet($id, 'recuperer'); }
    public function annulerObjet($id)   { $this->actionObjet($id, 'annuler'); }

    private function actionObjet($id, $action)
    {
        try { $this->api->post('/professionnels/objets/' . $id . '/' . $action, []); } catch (\Exception $e) {}
        header('Location: /professionnel/recuperation');
        exit;
    }

    public function scannerCode()
    {
        try { $this->api->post('/professionnels/objets/recuperer-par-code', ['code' => $_POST['code'] ?? '']); } catch (\Exception $e) {}
        header('Location: /professionnel/recuperation');
        exit;
    }
}
