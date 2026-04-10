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
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/utilisateurs');

            return view('admin.utilisateurs.index', [
                'utilisateurs' => $result['data'] ?? [],
                'page_title' => 'Gestion des utilisateurs'
            ]);
        } catch (\Exception $e) {
            return view('admin.utilisateurs.index', [
                'error' => $e->getMessage(),
                'utilisateurs' => [],
                'page_title' => 'Gestion des utilisateurs'
            ]);
        }
    }

    public function show($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);

            return view('admin.utilisateurs.show', [
                'utilisateur' => $result['data'] ?? [],
                'page_title' => 'Détail utilisateur'
            ]);
        } catch (\Exception $e) {
            redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $result = $this->api->get('/admin/utilisateurs/' . $id);

            return view('admin.utilisateurs.delete', [
                'utilisateur' => $result['data'] ?? [],
                'page_title' => 'Supprimer un utilisateur'
            ]);
        } catch (\Exception $e) {
            redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
        }
    }

    public function delete($id)
{
    try {
        $this->api->get('/admin/utilisateurs/delete/' . $id);
    } catch (\Exception $e) {}

    redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
}

    public function store()
    {
        try {
            $this->api->post('/auth/register', [
                'nom'         => $_POST['nom'] ?? '',
                'prenom'      => $_POST['prenom'] ?? '',
                'email'       => $_POST['email'] ?? '',
                'mot_de_passe' => $_POST['mot_de_passe'] ?? '',
                'role'        => $_POST['role'] ?? 'particulier'
            ]);
        } catch (\Exception $e) {}

        redirect('/UpcycleConnect-PA2526/frontend/public/admin/utilisateurs');
    }

    public function update($id) {}
}