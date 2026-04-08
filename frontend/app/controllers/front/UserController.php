<?php
namespace App\Controllers\Front;
use App\Services\ApiService;
class UserController
{
    private $api;
    public function __construct()
    {
        $this->api = new ApiService();
    }
    public function mesDemandes()
    {
        $annonces = [];
        $conteneurs = [];
        if (isset($_SESSION['user']['id'])) {
            try {
                $res      = $this->api->get('/annonces/user/' . $_SESSION['user']['id']) ?? [];
                $annonces = $res['data'] ?? $res;
            } catch (\Exception $e) {
                $annonces = [];
            }
            try {
                $res        = $this->api->get('/conteneurs/user/' . $_SESSION['user']['id']) ?? [];
                $conteneurs = $res['data'] ?? $res;
            } catch (\Exception $e) {
                $conteneurs = [];
            }
        }
        return view('front.demandes.index', [
            'title'      => 'Mes demandes - UpcycleConnect',
            'annonces'   => $annonces,
            'conteneurs' => $conteneurs,
        ]);
    }
    public function mesPrestations()
    {
        return view('front.mes-prestations.index', [
            'title' => 'Mes prestations - UpcycleConnect'
        ]);
    }
    public function paiements()
    {
        $paiements = [];
        if (isset($_SESSION['user']['id'])) {
            try {
                $paiements = $this->api->get('/paiements/' . $_SESSION['user']['id']);
            } catch (\Exception $e) {
                $paiements = [];
            }
        }
        return view('front.paiements.index', [
            'title'     => 'Paiements - UpcycleConnect',
            'paiements' => $paiements
        ]);
    }
    public function payer()
    {
        return view('front.paiements.payer', [
            'title' => 'Paiement - UpcycleConnect'
        ]);
    }
    public function tutorielDone()
    {
        if (isset($_SESSION['user'])) {
            $_SESSION['user']['tutoriel_vu'] = 1;
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function paiementSuccess()
{
    return view('front.paiements.success', [
        'title' => 'Paiement confirmé - UpcycleConnect',
        'type'  => $_GET['type'] ?? '',
        'id'    => $_GET['id'] ?? '',
    ]);
}
}