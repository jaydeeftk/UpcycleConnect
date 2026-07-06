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
                $_SESSION['token'] = $result['data']['token'] ?? null;

                $role = $_SESSION['user']['role'] ?? '';

                if ($role === 'admin') {
                    redirect('/admin/dashboard');
                } elseif ($role === 'salarie') {
                    redirect('/salaries/dashboard');
                } elseif ($role === 'professionnel') {
                    redirect('/professionnel');
                } else {
                    redirect('/');
                }
            }

        } catch (\Exception $e) {
            return view('front.auth.index', [
                'title' => 'Connexion - UpcycleConnect',
                'error' => 'Email ou mot de passe incorrect',
                'email' => $email
            ]);
        }
    }

    public function showRegister()
    {
        return view('front.auth.index', [
            'title' => 'Inscription - UpcycleConnect',
            'error' => null,
            'activeTab' => 'register'
        ]);
    }

    public function register()
    {
        $prenom = trim($_POST['prenom'] ?? '');
        $nomFamille = trim($_POST['nom'] ?? '');
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'particulier';

        $payload = [
            'nom' => $nomFamille,
            'prenom' => $prenom,
            'email' => $email,
            'telephone' => trim($_POST['telephone'] ?? ''),
            'mot_de_passe' => $password,
            'role' => $role,
        ];
        if ($role === 'professionnel') {
            $payload['nom_entreprise'] = $_POST['nom_entreprise'] ?? '';
            $payload['type'] = $_POST['type'] ?? 'artisan';
            $payload['siret'] = $_POST['siret'] ?? '';
        }

        try {
            $resp = $this->api->post('/auth/register', $payload);
            $confirm = $resp['data']['confirmation_required'] ?? $resp['confirmation_required'] ?? false;
            if ($confirm) {
                return view('front.auth.index', [
                    'title' => 'Inscription - UpcycleConnect',
                    'activeTab' => 'login',
                    'error' => null,
                    'success' => "Inscription réussie ! Un email d'activation vous a été envoyé. Cliquez sur le lien reçu pour activer votre compte.",
                ]);
            }
            redirect('/login');

        } catch (\Exception $e) {
            return view('front.auth.index', [
                'title' => 'Inscription - UpcycleConnect',
                'error' => $e->getMessage(),
                'activeTab' => 'register',
                'email' => $email,
                'prenom' => $prenom,
                'nom' => $nomFamille,
                'telephone' => trim($_POST['telephone'] ?? ''),
            ]);
        }
    }

    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        try {
            $this->api->get('/auth/confirmer', ['token' => $token]);
            return view('front.auth.index', [
                'title' => 'Compte activé - UpcycleConnect',
                'activeTab' => 'login',
                'error' => null,
                'success' => 'Votre compte est activé ! Vous pouvez maintenant vous connecter.',
            ]);
        } catch (\Exception $e) {
            return view('front.auth.index', [
                'title' => 'Activation - UpcycleConnect',
                'activeTab' => 'login',
                'error' => 'Lien de confirmation invalide ou compte déjà activé.',
            ]);
        }
    }

    public function showForgotPassword()
    {
        return view('front.auth.forgot', [
            'title'   => 'Mot de passe oublié - UpcycleConnect',
            'error'   => null,
            'success' => null,
        ]);
    }

    public function forgotPassword()
    {
        $email = $_POST['email'] ?? '';
        try {
            $this->api->post('/auth/mot-de-passe-oublie', ['email' => $email]);
        } catch (\Exception $e) {
        }
        return view('front.auth.forgot', [
            'title'   => 'Mot de passe oublié - UpcycleConnect',
            'error'   => null,
            'success' => "Si un compte existe pour cette adresse, un email de réinitialisation vient d'être envoyé. Pensez à vérifier vos spams.",
        ]);
    }

    public function showResetPassword()
    {
        return view('front.auth.reset', [
            'title' => 'Nouveau mot de passe - UpcycleConnect',
            'token' => $_GET['token'] ?? '',
            'error' => null,
        ]);
    }

    public function resetPassword()
    {
        $token    = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        try {
            $this->api->post('/auth/reinitialiser', ['token' => $token, 'mot_de_passe' => $password]);
            return view('front.auth.index', [
                'title'     => 'Mot de passe réinitialisé - UpcycleConnect',
                'activeTab' => 'login',
                'error'     => null,
                'success'   => 'Votre mot de passe a été réinitialisé. Vous pouvez maintenant vous connecter.',
            ]);
        } catch (\Exception $e) {
            return view('front.auth.reset', [
                'title' => 'Nouveau mot de passe - UpcycleConnect',
                'token' => $token,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function verifySiret($siret)
    {
        header('Content-Type: application/json');
        try {
            $clean = preg_replace('/\D/', '', (string) $siret);
            $r = $this->api->get('/siret/' . $clean);
            echo json_encode($r['data'] ?? $r);
        } catch (\Exception $e) {
            echo json_encode(['valid' => false, 'message' => 'Erreur de vérification']);
        }
    }

    public function logout()
    {
        session_destroy();
        redirect('/');
    }

    public function showAdminGate()
    {
        return view('auth.admin_login', [
            'layout' => 'blank',
            'title'  => 'Accès Restreint - Admin',
            'error'  => $_GET['error'] ?? null
        ]);
    }

    public function adminLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $result = $this->api->post('/auth/login', [
                'email' => $email,
                'mot_de_passe' => $password
            ]);

            if (isset($result['data']) && ($result['data']['role'] === 'admin' || $result['data']['role'] === 'superadmin')) {
                $_SESSION['user'] = $result['data'];
                $_SESSION['token'] = $result['data']['token'] ?? null;

                redirect('/admin/dashboard');
            } else {
                redirect('/admin-portal-access?error=Privilèges insuffisants');
            }

        } catch (\Exception $e) {
            redirect('/admin-portal-access?error=Identifiants invalides');
        }
    }
}