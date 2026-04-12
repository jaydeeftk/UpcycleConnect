<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class AnnonceController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
        $this->api->setToken($_SESSION["user"]["token"] ?? $_SESSION["token"] ?? "");
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/annonces');
            $rawData = isset($result['data']) ? $result['data'] : (is_array($result) ? $result : []);

            $annonces = array_map(function($item) {
                $newItem = array_change_key_case((array)$item, CASE_LOWER);
                if (isset($newItem['id_annonces'])) {
                    $newItem['id'] = $newItem['id_annonces'];
                }
                return $newItem;
            }, $rawData);

        } catch (\Exception $e) {
            $annonces = [];
        }

        return view('admin.annonces.index', [
            'annonces' => $annonces,
            'page_title' => 'Gestion des Annonces'
        ]);
    }

    public function validate($id)
    {
        try {
            $this->api->put('/admin/annonces/' . $id, ['statut' => 'validee']);
        } catch (\Exception $e) {}
        header('Location: /admin/annonces');
        exit;
    }

    public function reject($id)
    {
        try {
            $this->api->put('/admin/annonces/' . $id, ['statut' => 'rejetee']);
        } catch (\Exception $e) {}
        header('Location: /admin/annonces');
        exit;
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/admin/annonces/' . $id);
        } catch (\Exception $e) {}
        header('Location: /admin/annonces');
        exit;
    }
}