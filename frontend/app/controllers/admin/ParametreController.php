<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class ParametreController
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
            $result = $this->api->get('/admin/parametres/'); 

            $parametres = [];
            if (isset($result['data'])) {
                $parametres = $result['data'];
            } elseif (is_array($result)) {
                $parametres = $result;
            }

            return view('admin.parametres.index', [
                'parametres' => $parametres,
                'error' => $_SESSION['error'] ?? null
            ]);
        } catch (\Exception $e) {
            return view('admin.parametres.index', [
                'parametres' => [], 
                'error' => "Erreur de connexion API : " . $e->getMessage()
            ]);
        }
    }

    public function update()
    {
        try {
            $this->api->put('/admin/parametres/', [
                'nom_site'    => $_POST['nom_site'] ?? '',
                'email'       => $_POST['email'] ?? '',
                'description' => $_POST['description'] ?? '',
                'langue'      => $_POST['langue'] ?? 'Français',
                'fuseau'      => $_POST['fuseau'] ?? 'Europe/Paris',
            ]);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/admin/parametres');
    }

    public function updateMaintenance()
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