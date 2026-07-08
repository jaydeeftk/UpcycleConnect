<?php
namespace App\Controllers\Front;
use App\Services\ApiService;
class UserController
{
    private $api;
    public function __construct()
    {
        $this->api = new ApiService();
    }
    public function mesDemandes()
    {
        $annonces = [];
        $conteneurs = [];
        if (isset($_SESSION['user']['id'])) {
            try {
                $res      = $this->api->get('/annonces/user/' . $_SESSION['user']['id']) ?? [];
                $annonces = $res['data'] ?? $res;
            } catch (\Exception $e) {
                $annonces = [];
            }
            try {
                $res        = $this->api->get('/conteneurs/user/' . $_SESSION['user']['id']) ?? [];
                $conteneurs = $res['data'] ?? $res;
            } catch (\Exception $e) {
                $conteneurs = [];
            }
        }
        $deposees = [];
        foreach ($conteneurs as $c) {
            if (!empty($c['id_annonce'])) {
                $deposees[(int)$c['id_annonce']] = true;
            }
        }
        if ($deposees) {
            $annonces = array_values(array_filter($annonces, fn($a) => empty($deposees[(int)($a['id'] ?? 0)])));
        }
        return view('front.demandes.index', [
            'title'      => 'Mes demandes - UpcycleConnect',
            'annonces'   => $annonces,
            'conteneurs' => $conteneurs,
        ]);
    }
    public function mesPrestations()
    {
        $prestations = [];
        $commandesCatalogue = [];
        if (isset($_SESSION['user'])) {
            try {
                $r = $this->api->get('/prestations/demandes');
                $prestations = $r['data'] ?? (is_array($r) && !isset($r['success']) ? $r : []);
            } catch (\Exception $e) {
                $prestations = [];
            }
            foreach ($prestations as &$p) {
                $p['devis'] = [];
                if (($p['statut'] ?? '') === 'ouverte') {
                    try {
                        $rd = $this->api->get('/prestations/demandes/' . ($p['id'] ?? 0) . '/devis');
                        $p['devis'] = $rd['data'] ?? (is_array($rd) && !isset($rd['success']) ? $rd : []);
                    } catch (\Exception $e) {}
                }
            }
            unset($p);

            try {
                $r = $this->api->get('/services/mes-commandes');
                $commandesCatalogue = $r['data'] ?? (is_array($r) && !isset($r['success']) ? $r : []);
            } catch (\Exception $e) {
                $commandesCatalogue = [];
            }
        }
        return view('front.mes-prestations.index', [
            'title'              => 'Mes prestations réservées - UpcycleConnect',
            'prestations'        => $prestations,
            'commandes_catalogue'=> $commandesCatalogue,
            'token'              => $_SESSION['user']['token'] ?? '',
        ]);
    }

    public function annulerDemandePrestation($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        try {
            $this->api->post('/prestations/demandes/' . $id . '/annuler', []);
            $_SESSION['success'] = 'Demande annulée.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/mes-prestations');
    }
    public function paiements()
    {
        $paiements = [];
        $filtre = $_GET['type'] ?? 'tous';
        if (isset($_SESSION['user']['id'])) {
            try {
                $params    = ($filtre !== '' && $filtre !== 'tous') ? ['type' => $filtre] : [];
                $res       = $this->api->get('/paiements/' . $_SESSION['user']['id'], $params);
                $paiements = $res['data'] ?? (is_array($res) && !isset($res['success']) ? $res : []);
            } catch (\Exception $e) {
                $paiements = [];
            }
        }
        return view('front.paiements.index', [
            'title'     => 'Paiements - UpcycleConnect',
            'paiements' => $paiements,
            'filtre'    => $filtre,
        ]);
    }
    public function payer()
    {
        return view('front.paiements.payer', [
            'title' => 'Paiement - UpcycleConnect'
        ]);
    }
    public function tutorielDone()
    {
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['tutoriel_vu'] = 1;
            try { $this->api->post('/auth/tutoriel', []); } catch (\Exception $e) {}
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function paiementSuccess()
    {
        $commande = null;
        $sessionId = $_GET['session_id'] ?? '';
        if ($sessionId !== '') {
            try {
                $res = $this->api->get('/paiements/success', ['session_id' => $sessionId]);
                $commande = $res['data'] ?? null;
            } catch (\Exception $e) {
                $commande = null;
            }
        }
        return view('front.paiements.success', [
            'title'    => 'Paiement confirmé - UpcycleConnect',
            'commande' => $commande,
        ]);
    }

    public function facturePdf($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        $res = $this->api->getRaw('/factures/' . (int)$id . '/pdf');
        if (($res['code'] ?? 0) >= 400 || empty($res['body'])) {
            http_response_code(($res['code'] ?? 0) ?: 502);
            echo 'Facture indisponible';
            return;
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="facture-' . (int)$id . '.pdf"');
        echo $res['body'];
    }

    public function demandeRemboursement()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        try {
            $this->api->post('/remboursements', [
                'id_paiement' => (int)($_POST['id_paiement'] ?? 0),
                'motif'       => $_POST['motif'] ?? '',
            ]);
            $_SESSION['success'] = 'Votre demande de remboursement a bien été enregistrée.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/paiements');
    }

    public function historique()
    {
        $historique = [];
        if (isset($_SESSION['user']['id'])) {
            try {
                $r = $this->api->get('/historique/' . $_SESSION['user']['id']);
                $historique = $r['data'] ?? (is_array($r) && !isset($r['success']) ? $r : []);
            } catch (\Exception $e) {}
        }
        return view('front.historique.index', [
            'title'      => 'Historique des dépôts - UpcycleConnect',
            'historique' => $historique,
        ]);
    }

    public function contact()
    {
        return view('front.contact.index', [
            'title' => 'Contact - UpcycleConnect',
        ]);
    }

    public function sendContact()
    {
        if (!isset($_SESSION['user'])) {
            $_SESSION['contact_error'] = 'Vous devez être connecté.';
            header('Location: /contact');
            exit;
        }
        $contenu = trim($_POST['contenu'] ?? '');
        if (empty($contenu)) {
            $_SESSION['contact_error'] = 'Le message ne peut pas être vide.';
            header('Location: /contact');
            exit;
        }
        try {
            $this->api->post('/messages', ['contenu' => $contenu]);
            $_SESSION['contact_success'] = true;
        } catch (\Exception $e) {
            $_SESSION['contact_error'] = 'Erreur lors de l\'envoi.';
        }
        header('Location: /contact');
        exit;
    }

    public function notifications()
    {
        $notifications = [];
        $nonLues = 0;
        if (isset($_SESSION['user'])) {
            try {
                $r = $this->api->get('/notifications');
                $bloc = $r['data'] ?? $r;
                $notifications = $bloc['notifications'] ?? [];
                $nonLues = (int)($bloc['non_lues'] ?? 0);
            } catch (\Exception $e) {
                $notifications = [];
            }
        }
        return view('front.notifications.index', [
            'title'         => 'Notifications - UpcycleConnect',
            'notifications' => $notifications,
            'nonLues'       => $nonLues,
        ]);
    }

    public function notificationLue($id)
    {
        if (isset($_SESSION['user'])) {
            try { $this->api->post('/notifications/' . $id . '/lu', []); } catch (\Exception $e) {}
        }
        header('Location: /notifications');
        exit;
    }
}