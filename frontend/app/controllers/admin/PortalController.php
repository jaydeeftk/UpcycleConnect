<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class PortalController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function show()
    {
        if (isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? '') === 'admin') {
            redirect('/admin/dashboard');
        }
        $error = null;
        require __DIR__ . '/../../../ressources/views/admin/portal/index.php';
    }

    public function login()
    {
        $email    = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $error    = null;

        try {
            $result = $this->api->post('/auth/login', [
                'email'        => $email,
                'mot_de_passe' => $password,
            ]);

            $user = $result['data'] ?? $result ?? null;

            if ($user && ($user['role'] ?? '') === 'admin') {
                $_SESSION['user'] = $user;
                redirect('/admin/dashboard');
            }

            $error = 'Accès refusé : compte non administrateur.';
        } catch (\Exception $e) {
            $error = 'Identifiants incorrects.';
        }

        require __DIR__ . '/../../../ressources/views/admin/portal/index.php';
    }

    public function toggleMaintenance()
    {
        $file = '/tmp/.maintenance';
        if (file_exists($file)) {
            unlink($file);
        } else {
            file_put_contents($file, '1');
        }
        redirect('/admin/parametres?section=maintenance');
    }
}