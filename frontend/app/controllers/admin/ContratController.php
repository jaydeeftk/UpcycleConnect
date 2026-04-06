<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class ContratController
{
    private $api;
    public function __construct() { $this->api = new ApiService(); }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/contrats');
            return view('admin.contrats.index', ['contrats' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.contrats.index', ['contrats' => [], 'error' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/contrats/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/contrats');
    }
}