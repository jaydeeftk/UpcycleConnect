<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class AuthController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function showLogin()
    {
        return view('front.auth.index', [
            'title' => 'Connexion - UpcycleConnect',
            'error' => null
        ]);
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $result = $this->api->post('/auth/login', [
                'email' => $email,
                'mot_de_passe' => $password
            ]);

            if (isset($result['data'])) {
                $_SESSION['user'] = $result['data'];
                redirect('/UpcycleConnect-PA2526/frontend/public/');
            }

        } catch (\Exception $e) {
            return view('front.auth.index', [
                'title' => 'Connexion - UpcycleConnect',
                'error' => 'Email ou mot de passe incorrect'
            ]);
        }
    }

    public function showRegister()
    {
        return view('front.auth.index', [
            'title' => 'Inscription - UpcycleConnect',
            'error' => null
        ]);
    }

    public function register()
    {
        $nom = $_POST['nom'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'particulier';

        $nomParts = explode(' ', trim($nom), 2);
        $prenom = $nomParts[0] ?? '';
        $nomFamille = $nomParts[1] ?? '';

        try {
            $result = $this->api->post('/auth/register', [
                'nom' => $nomFamille,
                'prenom' => $prenom,
                'email' => $email,
                'mot_de_passe' => $password,
                'role' => $role
            ]);

            if (isset($result['data'])) {
                $_SESSION['user'] = $result['data'];
                $_SESSION['token'] = $result['data']['token'] ?? null;
                redirect('/UpcycleConnect-PA2526/frontend/public/');
            }

        } catch (\Exception $e) {
            return view('front.auth.index', [
                'title' => 'Inscription - UpcycleConnect',
                'error' => 'Erreur lors de la création du compte : ' . $e->getMessage()
            ]);
        }
    }

    public function logout()
    {
        session_destroy();
        redirect('/UpcycleConnect-PA2526/frontend/public/');
    }
}