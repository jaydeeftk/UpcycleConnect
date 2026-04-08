<?php
namespace App\Controllers\Front;
use App\Services\ApiService;
class EvenementController
{
    private $api;
    public function __construct()
    {
        $this->api = new ApiService();
    }
    public function index()
    {
        $evenements = [];
        try {
            $res        = $this->api->get('/evenements');
            $evenements = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $evenements = [];
        }
        return view('front.evenements.index', [
            'title'      => 'Événements - UpcycleConnect',
            'evenements' => $evenements,
        ]);
    }
    public function show($id)
    {
        $evenement = [];
        try {
            $userId    = $_SESSION['user']['id'] ?? 0;
            $res       = $this->api->get('/evenements/' . $id . '?user_id=' . $userId);
            $evenement = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $evenement = [];
        }
        return view('front.evenements.detail', [
            'title'     => 'Détail événement - UpcycleConnect',
            'evenement' => $evenement,
        ]);
    }
    public function participer($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        try {
            $this->api->post('/evenements/' . $id . '/participer', [
                'id_utilisateur' => $_SESSION['user']['id'] ?? 0,
            ]);
            redirect('/evenements/' . $id . '?success=1');
        } catch (\Exception $e) {
            redirect('/evenements/' . $id . '?error=' . urlencode($e->getMessage()));
        }
    }
    public function desinscrire($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        try {
            $this->api->post('/evenements/' . $id . '/desinscrire', [
                'id_utilisateur' => $_SESSION['user']['id'] ?? 0,
            ]);
            redirect('/evenements/' . $id . '?success_desinscription=1');
        } catch (\Exception $e) {
            redirect('/evenements/' . $id . '?error=' . urlencode($e->getMessage()));
        }
    }
}