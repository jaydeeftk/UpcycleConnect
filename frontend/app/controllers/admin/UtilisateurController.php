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
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/utilisateurs');
            return view('admin.utilisateurs.index', ['utilisateurs' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.utilisateurs.index', ['utilisateurs' => [], 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);
            $utilisateur = $result['data'] ?? [];
            return view('admin.utilisateurs.show', ['utilisateur' => $utilisateur]);
        } catch (\Exception $e) {
            return view('admin.utilisateurs.show', ['utilisateur' => [], 'error' => $e->getMessage()]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/admin/utilisateurs', [
                'nom'          => $_POST['nom'] ?? '',
                'prenom'       => $_POST['prenom'] ?? '',
                'email'        => $_POST['email'] ?? '',
                'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
                'telephone'    => $_POST['telephone'] ?? '',
                'adresse'      => $_POST['adresse'] ?? '',
                'statut'       => $_POST['statut'] ?? 'actif',
            ]);
        } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
    }

    public function confirmDelete($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);
            $utilisateur = $result['data'] ?? ['id' => $id, 'nom' => '', 'prenom' => ''];
        } catch (\Exception $e) {
            $utilisateur = ['id' => $id, 'nom' => '', 'prenom' => ''];
        }
        return view('admin.utilisateurs.delete', ['utilisateur' => $utilisateur]);
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/utilisateurs/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
    }
}