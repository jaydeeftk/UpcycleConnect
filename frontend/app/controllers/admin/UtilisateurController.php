<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class UtilisateurController
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
            $result = $this->api->get('/admin/utilisateurs');
            $utilisateurs = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $utilisateurs = [];
        }
        return view('admin.utilisateurs.index', ['utilisateurs' => $utilisateurs, 'page_title' => 'Utilisateurs']);
    }

    public function show($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);
            $utilisateur = isset($result['data']) ? $result['data'] : ($result ?? []);
        } catch (\Exception $e) {
            $utilisateur = [];
        }
        return view('admin.utilisateurs.show', ['utilisateur' => $utilisateur, 'page_title' => 'Détail utilisateur']);
    }

    public function create()
    {
        return view('admin.utilisateurs.create', ['page_title' => 'Ajouter un utilisateur']);
    }

    public function store()
    {
        try {
            $this->api->post('/auth/register', [
                'nom'          => $_POST['nom'] ?? '',
                'prenom'       => $_POST['prenom'] ?? '',
                'email'        => $_POST['email'] ?? '',
                'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
                'role'         => $_POST['role'] ?? 'particulier'
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/utilisateurs');
    }

    public function update($id)
    {
        try {
            $this->api->put('/admin/utilisateurs/' . $id, [
                'nom'    => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email'  => $_POST['email'] ?? '',
                'statut' => $_POST['statut'] ?? 'actif',
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/utilisateurs/' . $id);
    }

    public function confirmDelete($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);
            $utilisateur = isset($result['data']) ? $result['data'] : ($result ?? ['id' => $id]);
        } catch (\Exception $e) {
            $utilisateur = ['id' => $id];
        }
        return view('admin.utilisateurs.delete', ['utilisateur' => $utilisateur, 'page_title' => 'Supprimer']);
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/utilisateurs/' . $id); } catch (\Exception $e) {}
        redirect('/admin/utilisateurs');
    }

    public function statut($id)
    {
        try {
            $this->api->put('/admin/utilisateurs/' . $id . '/statut', [
                'statut' => $_POST['statut'] ?? 'actif',
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/utilisateurs/' . $id);
    }

    public function role($id)
    {
        try {
            $this->api->put('/admin/utilisateurs/' . $id . '/role', [
                'role' => $_POST['role'] ?? 'particulier',
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/utilisateurs/' . $id);
    }
}