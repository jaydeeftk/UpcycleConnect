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
            return view('admin.utilisateurs.show', ['utilisateur' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
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
        return view('admin.utilisateurs.delete', ['id' => $id]);
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/utilisateurs/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
    }
}