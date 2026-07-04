<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class ConteneurController
{
    private $api;

    public function __construct()
    {
        \App\Middleware\AdminMiddleware::check();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/conteneurs');
            $conteneurs = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $conteneurs = [];
        }

        return view('admin.conteneurs.index', [
            'conteneurs'    => $conteneurs,
            'page_title'    => 'Conteneurs & Box',
            'page_subtitle' => 'Gérez les points de collecte et leur taux de remplissage',
        ]);
    }

    public function show($id)
    {
        $conteneur = null;
        $demandes = [];
        try {
            $result = $this->api->get('/admin/conteneurs');
            $list = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
            foreach ($list as $c) {
                if ((int)($c['id'] ?? 0) === (int)$id) { $conteneur = $c; break; }
            }
            $dres = $this->api->get('/admin/demandes');
            $all = isset($dres['data']) && is_array($dres['data']) ? $dres['data'] : (is_array($dres) && !isset($dres['success']) ? $dres : []);
            foreach ($all as $d) {
                if ((int)($d['id_conteneur'] ?? 0) === (int)$id) { $demandes[] = $d; }
            }
        } catch (\Exception $e) {}

        if (!$conteneur) {
            http_response_code(404);
            return view('errors.404');
        }

        return view('admin.conteneurs.show', [
            'conteneur'  => $conteneur,
            'demandes'   => $demandes,
            'page_title' => 'Détail conteneur',
        ]);
    }

    public function store()
    {
        try {
            $this->api->post('/admin/conteneurs', [
                'localisation' => $_POST['localisation'] ?? '',
                'capacite'     => (int)($_POST['capacite'] ?? 0),
                'statut'       => $_POST['statut'] ?? 'disponible',
                'hauteur'      => (float)($_POST['hauteur'] ?? 0),
                'largeur'      => (float)($_POST['largeur'] ?? 0),
                'longueur'     => (float)($_POST['longueur'] ?? 0),
            ]);
        } catch (\Exception $e) {}
        header('Location: /admin/conteneurs');
        exit;
    }

    public function update($id)
    {
        try {
            $this->api->put('/admin/conteneurs/' . $id, [
                'localisation' => $_POST['localisation'] ?? '',
                'capacite'     => (int)($_POST['capacite'] ?? 0),
                'statut'       => $_POST['statut'] ?? 'disponible',
                'hauteur'      => (float)($_POST['hauteur'] ?? 0),
                'largeur'      => (float)($_POST['largeur'] ?? 0),
                'longueur'     => (float)($_POST['longueur'] ?? 0),
            ]);
        } catch (\Exception $e) {}
        header('Location: /admin/conteneurs');
        exit;
    }

    public function accept($id)
    {
        try { $this->api->post('/admin/conteneurs/demandes/' . $id . '/accept', []); } catch (\Exception $e) {}
        header('Location: /admin/conteneurs');
        exit;
    }

    public function refuse($id)
    {
        try { $this->api->post('/admin/conteneurs/demandes/' . $id . '/refuse', []); } catch (\Exception $e) {}
        header('Location: /admin/conteneurs');
        exit;
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/conteneurs/' . $id); } catch (\Exception $e) {}
        header('Location: /admin/conteneurs');
        exit;
    }
}
