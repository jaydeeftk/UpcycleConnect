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

        $facturation = [];
        try {
            $r = $this->api->get('/professionnels/facturation');
            $facturation = $r['data'] ?? [];
        } catch (\Exception $e) {}

        return view('professionnel.dashboard', [
            'profil'   => $profil,
            'projets'  => $projets,
            'favoris'  => $favoris,
            'contrats' => $contrats,
            'facturation' => $facturation,
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

    // Detail projet : carte avec form edition + form ajout etape (multipart)
    // + liste des etapes existantes avec photos avant/apres.
    public function showProjet($id)
    {
        if (!isset($_SESSION['user'])) { redirect('/login'); }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        $projets = [];
        $etapes  = [];
        try {
            $r = $this->api->get('/professionnels/projets');
            $projets = isset($r['data']) && is_array($r['data']) ? $r['data'] : [];
        } catch (\Exception $e) {}
        $projet = null;
        foreach ($projets as $p) { if ((int)($p['id'] ?? 0) === (int)$id) { $projet = $p; break; } }
        if ($projet === null) {
            $_SESSION['error'] = 'Projet introuvable.';
            redirect('/professionnel'); return;
        }
        try {
            $r = $this->api->get('/professionnels/projets/' . (int)$id);
            $etapes = isset($r['data']) && is_array($r['data']) ? $r['data'] : [];
        } catch (\Exception $e) {}
        return view('professionnel.projets.detail', [
            'page_title' => 'Projet : ' . ($projet['titre'] ?? ''),
            'layout'     => 'raw',
            'projet'     => $projet,
            'etapes'     => $etapes,
        ]);
    }

    public function updateProjet($id)
    {
        if (!isset($_SESSION['user'])) { redirect('/login'); }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        try {
            $this->api->put('/professionnels/projets/' . (int)$id, [
                'titre'       => $_POST['titre'] ?? '',
                'description' => $_POST['description'] ?? '',
            ]);
            $_SESSION['success'] = 'Projet mis à jour.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/professionnel/projets/' . (int)$id);
    }

    // Ajout d'une etape avec photos multipart avant/apres. Le PHP ecrit les
    // fichiers dans public/uploads/projets/{idProjet}/{idEtape}/ puis POSTe
    // les URLs resolues a l'API qui INSERE les Medias avec ownership check.
    public function ajouterEtape($id)
    {
        if (!isset($_SESSION['user'])) { redirect('/login'); }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        $idProjet = (int)$id;
        try {
            $r = $this->api->post('/professionnels/projets/' . $idProjet . '/etapes', [
                'nom'         => $_POST['nom'] ?? '',
                'description' => $_POST['description'] ?? '',
                'visuel'      => '',
            ]);
            $idEtape = (int)($r['data']['id'] ?? 0);
            if ($idEtape > 0) {
                foreach (['avant' => 'photo_avant', 'apres' => 'photo_apres'] as $type => $field) {
                    if (!empty($_FILES[$field]) && ($_FILES[$field]['error'] ?? 99) === UPLOAD_ERR_OK) {
                        $url = $this->stockerPhotoEtape($idProjet, $idEtape, $_FILES[$field]);
                        if ($url) {
                            $this->api->post('/professionnels/projets/' . $idProjet . '/etapes/' . $idEtape . '/photos', [
                                'url'        => $url,
                                'type_photo' => $type,
                            ]);
                        }
                    }
                }
            }
            $_SESSION['success'] = 'Étape ajoutée.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/professionnel/projets/' . $idProjet);
    }

    public function supprimerEtape($idProjet, $idEtape)
    {
        if (!isset($_SESSION['user'])) { redirect('/login'); }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        try {
            $this->api->delete('/professionnels/projets/' . (int)$idProjet . '/etapes/' . (int)$idEtape);
            $_SESSION['success'] = 'Étape supprimée.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/professionnel/projets/' . (int)$idProjet);
    }

    private function stockerPhotoEtape($idProjet, $idEtape, array $file)
    {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime = mime_content_type($file['tmp_name'] ?? '') ?: '';
        if (!isset($allowed[$mime])) { return null; }
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) { return null; }
        $ext = $allowed[$mime];
        $dir = __DIR__ . '/../../../public/uploads/projets/' . (int)$idProjet . '/' . (int)$idEtape;
        if (!is_dir($dir) && !mkdir($dir, 0o755, true)) { return null; }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) { return null; }
        return '/uploads/projets/' . (int)$idProjet . '/' . (int)$idEtape . '/' . $name;
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

    // ─── Annonces pro ────────────────────────────────────────────────────────

    public function annonces()
    {
        $annonces    = [];
        $mesAnnonces = [];
        try {
            $r        = $this->api->get('/annonces');
            $annonces = $r['data'] ?? (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        try {
            $r           = $this->api->get('/annonces/user');
            $mesAnnonces = $r['data'] ?? (is_array($r) && !isset($r['success']) ? $r : []);
        } catch (\Exception $e) {}

        $localisation = trim($_GET['localisation'] ?? '');
        $type         = $_GET['type'] ?? 'tous';

        if ($localisation !== '') {
            $annonces = array_values(array_filter($annonces, function ($a) use ($localisation) {
                return stripos($a['ville'] ?? '', $localisation) !== false
                    || stripos($a['code_postal'] ?? '', $localisation) !== false;
            }));
        }
        if ($type !== 'tous') {
            $annonces = array_values(array_filter($annonces, fn($a) => ($a['type_annonce'] ?? '') === $type));
        }

        return view('professionnel.annonces.index', [
            'annonces'     => $annonces,
            'mesAnnonces'  => $mesAnnonces,
            'localisation' => $localisation,
            'page_title'   => 'Annonces',
            'layout'       => 'raw',
        ]);
    }

    public function createAnnonce()
    {
        return view('professionnel.annonces.create', [
            'page_title' => 'Nouvelle annonce',
            'layout'     => 'raw',
        ]);
    }

    public function storeAnnonce()
    {
        $data = [
            'titre'        => $_POST['titre']        ?? '',
            'categorie'    => $_POST['categorie']    ?? '',
            'description'  => $_POST['description']  ?? '',
            'etat'         => $_POST['etat']         ?? '',
            'type_annonce' => $_POST['type_annonce'] ?? 'don',
            'prix'         => $_POST['type_annonce'] === 'vente' ? (float)($_POST['prix'] ?? 0) : 0,
            'ville'        => trim($_POST['ville']        ?? ''),
            'code_postal'  => trim($_POST['code_postal']  ?? ''),
            'user_id'      => $_SESSION['user']['id'] ?? 0,
        ];
        if (!preg_match('/^\d{5}$/', $data['code_postal'])) {
            return view('professionnel.annonces.create', [
                'page_title' => 'Nouvelle annonce',
                'layout'     => 'raw',
                'error'      => 'Code postal invalide : 5 chiffres attendus.',
            ]);
        }
        try {
            $this->api->post('/annonces/create', $data);
            $_SESSION['success'] = 'Annonce soumise avec succès. Elle sera vérifiée avant publication.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/professionnel/annonces');
    }

    public function annulerAnnonce($id)
    {
        try { $this->api->post('/annonces/' . $id . '/annuler', []); } catch (\Exception $e) {}
        redirect('/professionnel/annonces');
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
