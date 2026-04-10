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
        if (isset($_SESSION['user']) && ($_SESSION['user']['statut'] ?? '') === 'admin') {
            redirect('/UpcycleConnect-PA2526/frontend/public/admin/dashboard');
        }
        require __DIR__ . '/../../../ressources/views/admin/portal/index.php';
    }

    public function login()
    {
        $email    = $_POST['email']    ?? '';
        $password = $_POST['password'] ?? '';

        try {
            $result = $this->api->post('/auth/login', [
                'email'        => $email,
                'mot_de_passe' => $password,
            ]);

            $user = $result['data'] ?? $result ?? null;

            if ($user && ($user['statut'] ?? '') === 'admin') {
                $_SESSION['user'] = $user;
                redirect('/UpcycleConnect-PA2526/frontend/public/admin/dashboard');
            }

            $error = 'Accès refusé : compte non administrateur.';
        } catch (\Exception $e) {
            $error = 'Identifiants incorrects.';
        }

        require __DIR__ . '/../../../ressources/views/admin/portal/index.php';
    }

    public function toggleMaintenance()
    {
        $file = __DIR__ . '/../../../../.maintenance';
        if (file_exists($file)) {
            unlink($file);
        } else {
            file_put_contents($file, '1');
        }
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/parametres');
    }
}