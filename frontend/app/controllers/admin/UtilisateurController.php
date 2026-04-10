<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class UtilisateurController
{
    private $api;
    public function __construct()
    {
        \App\Middleware\AdminMiddleware::handle();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }
    public function index()
    {
        try {
            $result = $this->api->get('/admin/utilisateurs');
            $utilisateurs = isset($result['data']) ? $result['data'] : ($result ?? []);
        } catch (\Exception $e) { $utilisateurs = []; }
        return view('admin.utilisateurs.index', ['utilisateurs' => $utilisateurs, 'page_title' => 'Utilisateurs']);
    }
    public function show($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);
            $utilisateur = isset($result['data']) ? $result['data'] : ($result ?? []);
        } catch (\Exception $e) {
            redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
            return;
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
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
    }
    public function update($id)
    {
        try {
            $this->api->put('/admin/utilisateurs/update/' . $id, [
                'nom'    => $_POST['nom'] ?? '',
                'prenom' => $_POST['prenom'] ?? '',
                'email'  => $_POST['email'] ?? '',
                'statut' => $_POST['statut'] ?? 'actif',
            ]);
        } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs/' . $id);
    }
    public function confirmDelete($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);
            $utilisateur = isset($result['data']) ? $result['data'] : ($result ?? []);
        } catch (\Exception $e) {
            redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
            return;
        }
        return view('admin.utilisateurs.delete', ['utilisateur' => $utilisateur, 'page_title' => 'Supprimer']);
    }
    public function delete($id)
    {
        try { $this->api->delete('/admin/utilisateurs/delete/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
    }
}