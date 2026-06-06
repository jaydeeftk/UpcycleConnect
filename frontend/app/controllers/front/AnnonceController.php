<?php
namespace App\Controllers\Front;
use App\Services\ApiService;
class AnnonceController
{
    private $api;
    public function __construct()
    {
        $this->api = new ApiService();
        // Le token doit voyager sur TOUTES les requêtes authentifiées (annuler,
        // vendre, mes-annonces) — sans lui le serveur renvoie 401. (Auparavant
        // absent : annuler échouait silencieusement.)
        if (!empty($_SESSION['user']['token'])) {
            $this->api->setToken($_SESSION['user']['token']);
        }
    }
    public function annuler($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        try { $this->api->post('/annonces/' . $id . '/annuler', []); } catch (\Exception $e) {}
        redirect('/mes-annonces');
    }

    public function vendre($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        try { $this->api->post('/annonces/' . $id . '/vendre', []); } catch (\Exception $e) {}
        redirect('/mes-annonces');
    }

    public function mesAnnonces()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $annonces = [];
        try {
            $res = $this->api->get('/annonces/user');
            $annonces = isset($res['data']) && is_array($res['data']) ? $res['data'] : (is_array($res) && !isset($res['success']) ? $res : []);
        } catch (\Exception $e) {}
        return view('front.annonces.mes', [
            'title'    => 'Mes annonces - UpcycleConnect',
            'annonces' => $annonces,
        ]);
    }

    public function show($id)
    {
        $annonce = [];
        try {
            $res     = $this->api->get('/annonces/' . $id);
            $annonce = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $annonce = [];
        }
        return view('front.annonces.show', [
            'title'   => ($annonce['titre'] ?? 'Annonce') . ' - UpcycleConnect',
            'annonce' => $annonce,
        ]);
    }

    public function index()
    {
        $annonces = [];
        try {
            $res      = $this->api->get('/annonces');
            $annonces = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $annonces = [];
        }
        return view('front.annonces.index', [
            'title'    => 'Toutes les annonces - UpcycleConnect',
            'annonces' => $annonces,
        ]);
    }
    public function create()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        return view('front.annonces.create', [
            'title' => 'Déposer une annonce - UpcycleConnect',
        ]);
    }
    public function toggleFavori($id)
    {
        if (!isset($_SESSION['user'])) { redirect('/login'); }
        $this->api->setToken($_SESSION['user']['token'] ?? '');
        try { $this->api->post('/professionnels/favoris/' . $id, []); } catch (\Exception $e) {}
        redirect('/annonces/' . $id);
    }

    public function store()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $data = [
            'titre'        => $_POST['titre'] ?? '',
            'categorie'    => $_POST['categorie'] ?? '',
            'description'  => $_POST['description'] ?? '',
            'etat'         => $_POST['etat'] ?? '',
            'type_annonce' => $_POST['type_annonce'] ?? 'don',
            'prix'         => $_POST['type_annonce'] === 'vente' ? (float)($_POST['prix'] ?? 0) : 0,
            'ville'        => $_POST['ville'] ?? '',
            'code_postal'  => $_POST['code_postal'] ?? '',
            'user_id'      => $_SESSION['user']['id'] ?? 0,
        ];
        try {
            $this->api->post('/annonces/create', $data);
            return view('front.annonces.create', [
                'title'   => 'Déposer une annonce - UpcycleConnect',
                'success' => 'Votre annonce a bien été soumise ! Elle sera vérifiée par notre équipe avant publication.',
            ]);
        } catch (\Exception $e) {
            return view('front.annonces.create', [
                'title' => 'Déposer une annonce - UpcycleConnect',
                'error' => $e->getMessage(),
            ]);
        }
    }
}