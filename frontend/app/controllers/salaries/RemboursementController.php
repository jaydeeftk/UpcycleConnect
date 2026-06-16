<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class RemboursementController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();

        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        if (isset($_SESSION['token'])) {
            $this->api->setToken($_SESSION['token']);
        }
    }

    public function index()
    {
        try {
            $result   = $this->api->get('/salaries/remboursements');
            $demandes = $result['data'] ?? (is_array($result) && !isset($result['success']) ? $result : []);

            return view('salaries.remboursements.index', [
                'demandes'      => $demandes,
                'page_title'    => 'Remboursements',
                'page_subtitle' => 'Traitez les demandes de remboursement',
            ]);
        } catch (\Exception $e) {
            return view('salaries.remboursements.index', [
                'error'         => $e->getMessage(),
                'demandes'      => [],
                'page_title'    => 'Remboursements',
                'page_subtitle' => 'Traitez les demandes de remboursement',
            ]);
        }
    }

    public function approuver($id)
    {
        try {
            $this->api->post('/salaries/remboursements/' . $id . '/approuver', []);
            $_SESSION['success'] = t('sal_flash_remb_approuve', 'Remboursement effectué.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/salaries/remboursements');
    }

    public function refuser($id)
    {
        try {
            $this->api->post('/salaries/remboursements/' . $id . '/refuser', []);
            $_SESSION['success'] = t('sal_flash_remb_refuse', 'Demande refusée.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/salaries/remboursements');
    }

    public function direct()
    {
        try {
            $this->api->post('/salaries/remboursements/direct', [
                'id_paiement' => (int)($_POST['id_paiement'] ?? 0),
                'motif'       => $_POST['motif'] ?? '',
            ]);
            $_SESSION['success'] = t('sal_flash_remb_direct', 'Remboursement direct effectué.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/salaries/remboursements');
    }
}
