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
        return view('front.conteneurs.create', [
            'title'      => 'Déposer un objet - UpcycleConnect',
            'conteneurs' => $conteneurs,
        ]);
    }
    public function store()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $data = [
            'type_objet'   => $_POST['type_objet'] ?? '',
            'description'  => $_POST['description'] ?? '',
            'etat_usure'   => $_POST['etat_usure'] ?? '',
            'conteneur_id' => (int)($_POST['conteneur_id'] ?? 0),
            'date_depot'   => $_POST['date_depot'] ?? '',
            'destination'  => $_POST['destination'] ?? 'don',
            'prix_vente'   => $_POST['destination'] === 'vente' ? (float)($_POST['prix_vente'] ?? 0) : 0,
            'user_id'      => $_SESSION['user']['id'] ?? 0,
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
                'error'      => 'Une erreur est survenue lors de l\'envoi de votre demande. Veuillez réessayer.',
            ]);
        }
    }
}
