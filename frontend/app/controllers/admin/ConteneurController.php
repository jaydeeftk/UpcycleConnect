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

    public function store()
    {
        try {
            $this->api->post('/admin/conteneurs', [
                'localisation' => $_POST['localisation'] ?? '',
                'capacite'     => (int)($_POST['capacite'] ?? 0),
                'statut'       => $_POST['statut'] ?? 'disponible',
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
