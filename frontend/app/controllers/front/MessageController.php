<?php
namespace App\Controllers\Front;

use App\Services\ApiService;

class MessageController
{
    private $api;

    public function __construct()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }

    public function index()
    {
        return view('front.messages.index', [
            'page_title' => 'Mes messages',
            'token'      => $_SESSION['user']['token'] ?? '',
            'user_id'    => $_SESSION['user']['id'] ?? 0,
        ]);
    }

    public function historique()
    {
        $tickets = [];
        try {
            $result = $this->api->get('/tickets/historique');
            $tickets = $result['data'] ?? (is_array($result) ? $result : []);
        } catch (\Exception $e) {}

        return view('front.messages.historique', [
            'tickets'    => $tickets,
            'page_title' => 'Historique de mes tickets',
            'token'      => $_SESSION['user']['token'] ?? '',
            'user_id'    => $_SESSION['user']['id'] ?? 0,
        ]);
    }

    public function historiqueDetail($id)
    {
        return view('front.messages.historique_detail', [
            'id_ticket'  => (int)$id,
            'page_title' => 'Ticket #' . (int)$id,
            'token'      => $_SESSION['user']['token'] ?? '',
            'user_id'    => $_SESSION['user']['id'] ?? 0,
        ]);
    }
}
