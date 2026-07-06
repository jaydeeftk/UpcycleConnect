<?php
namespace App\Controllers\Front;
use App\Services\ApiService;
class ConteneurController
{
    private $api;
    public function __construct()
    {
        $this->api = new ApiService();
    }
    public function create()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $conteneurs = [];
        try {
            $conteneurs = $this->api->get('/conteneurs');
        } catch (\Exception $e) {
            $conteneurs = [];
        }

        $idAnnonce = (int)($_GET['id_annonce'] ?? 0);
        $annonceChoisie = null;
        $annoncesEligibles = [];

        if ($idAnnonce > 0) {
            try {
                $res = $this->api->get('/annonces/' . $idAnnonce);
                $annonceChoisie = $res['data'] ?? $res;
            } catch (\Exception $e) {
                $annonceChoisie = null;
            }
        } else {
            try {
                $res = $this->api->get('/conteneurs/annonces-eligibles');
                $annoncesEligibles = $res['data'] ?? (is_array($res) ? $res : []);
            } catch (\Exception $e) {
                $annoncesEligibles = [];
            }
        }

        return view('front.conteneurs.create', [
            'title'             => 'Déposer un objet - UpcycleConnect',
            'conteneurs'        => $conteneurs,
            'id_annonce'        => $idAnnonce,
            'annonce_choisie'   => $annonceChoisie,
            'annonces_eligibles'=> $annoncesEligibles,
        ]);
    }
    public function store()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $data = [
            'etat_usure'   => $_POST['etat_usure'] ?? '',
            'conteneur_id' => (int)($_POST['conteneur_id'] ?? 0),
            'date_depot'   => $_POST['date_depot'] ?? '',
            'photo_url'    => $_POST['photo_url'] ?? '',
            'id_annonce'   => (int)($_POST['id_annonce'] ?? 0),
        ];
        try {
            $this->api->post('/conteneurs/demandes', $data);
            $conteneurs = [];
            try {
                $conteneurs = $this->api->get('/conteneurs');
            } catch (\Exception $e) {}
            return view('front.conteneurs.create', [
                'title'      => 'Déposer un objet - UpcycleConnect',
                'conteneurs' => $conteneurs,
                'success'    => 'Votre demande de dépôt a bien été envoyée ! Notre équipe vous contactera sous 24 à 48h avec votre code d\'accès.',
            ]);
        } catch (\Exception $e) {
            $conteneurs = [];
            try {
                $conteneurs = $this->api->get('/conteneurs');
            } catch (\Exception $e2) {}
            return view('front.conteneurs.create', [
                'title'      => 'Déposer un objet - UpcycleConnect',
                'conteneurs' => $conteneurs,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
