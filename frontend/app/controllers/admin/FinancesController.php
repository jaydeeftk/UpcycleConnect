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

        $remboursements = [];
        try {
            $res = $this->api->get('/remboursements');
            $remboursements = $res['data'] ?? (is_array($res) && !isset($res['success']) ? $res : []);
        } catch (\Exception $e) {
            $remboursements = [];
        }

        return view('admin.finances.index', [
            'finances'        => $finances,
            'remboursements'  => $remboursements,
            'page_title'      => 'Tableau de bord financier',
            'page_subtitle'   => 'Suivi du chiffre d\'affaires et des commissions'
        ]);
    }

    public function commissions()
    {
        $commissions = [];
        try {
            $result = $this->api->get('/admin/commissions');
            $commissions = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $commissions = [];
        }

        return view('admin.finances.commissions', [
            'commissions'   => $commissions,
            'page_title'    => 'Détail des commissions',
            'page_subtitle' => 'Répartition entre UpcycleConnect et les vendeurs/prestataires',
        ]);
    }
}