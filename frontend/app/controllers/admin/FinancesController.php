<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class FinancesController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/finances');
            $finances = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $finances = [];
        }

        return view('admin.finances.index', [
            'finances' => $finances,
            'page_title' => 'Tableau de bord financier',
            'page_subtitle' => 'Suivi du chiffre d\'affaires et des commissions'
        ]);
    }
}