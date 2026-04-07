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
        $result = $this->api->get('/admin/parametres');
        
        echo "<pre>";
        var_dump($result); 
        echo "</pre>";
        die();
        
        return view('admin.parametres.index', ['parametres' => $result['data'] ?? []]);
    } catch (\Exception $e) {
        return view('admin.parametres.index', ['parametres' => [], 'error' => $e->getMessage()]);
    }
}

    public function update()
    {
        try {
            $this->api->put('/admin/parametres', [
                'nom_site'    => $_POST['nom_site'] ?? '',
                'email'       => $_POST['email'] ?? '',
                'description' => $_POST['description'] ?? '',
                'langue'      => $_POST['langue'] ?? 'Français',
                'fuseau'      => $_POST['fuseau'] ?? 'Europe/Paris',
            ]);
        } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/parametres');
    }
    
    public function updateMaintenance()
    {
        try {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            if (!isset($data['maintenance_mode'])) {
                throw new \Exception("Données manquantes");
            }

            $result = $this->api->put('/admin/parametres/', [
                'maintenance_mode' => $data['maintenance_mode']
            ]);

            header('Content-Type: application/json');
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}