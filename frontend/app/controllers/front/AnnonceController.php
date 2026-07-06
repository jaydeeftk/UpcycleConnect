<?php
namespace App\Controllers\Front;
use App\Services\ApiService;
class AnnonceController
{
    private $api;
    public function __construct()
    {
        $this->api = new ApiService();
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

    public function reserver($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        try {
            $this->api->post('/annonces/' . $id . '/reserver', []);
            $_SESSION['success'] = 'Don réservé avec succès. Une conversation a été ouverte avec le déposant.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/annonces/' . $id);
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

        $localisation = trim($_GET['localisation'] ?? '');
        if ($localisation !== '') {
            $annonces = array_values(array_filter($annonces, function ($a) use ($localisation) {
                $ville = $a['ville'] ?? '';
                $cp    = $a['code_postal'] ?? '';
                return stripos($ville, $localisation) !== false || stripos($cp, $localisation) !== false;
            }));
        }

        return view('front.annonces.index', [
            'title'    => 'Toutes les annonces - UpcycleConnect',
            'annonces' => $annonces,
            'localisation' => $localisation,
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
            'ville'        => trim($_POST['ville'] ?? ''),
            'code_postal'  => trim($_POST['code_postal'] ?? ''),
            'user_id'      => $_SESSION['user']['id'] ?? 0,
        ];
        if (!preg_match('/^\d{5}$/', $data['code_postal'])) {
            return view('front.annonces.create', [
                'title' => 'Déposer une annonce - UpcycleConnect',
                'error' => 'Code postal invalide : 5 chiffres attendus.',
            ]);
        }
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