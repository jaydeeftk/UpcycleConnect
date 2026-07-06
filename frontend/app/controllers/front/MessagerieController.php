<?php
namespace App\Controllers\Front;

use App\Services\ApiService;

class MessagerieController
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
        $conversations = [];
        try {
            $result = $this->api->get('/conversations');
            $conversations = $result['data'] ?? (is_array($result) ? $result : []);
        } catch (\Exception $e) {}

        return view('front.messagerie.index', [
            'conversations' => $conversations,
            'title'         => 'Messagerie - UpcycleConnect',
            'token'         => $_SESSION['user']['token'] ?? '',
            'user_id'       => $_SESSION['user']['id'] ?? 0,
        ]);
    }

    public function demarrer()
    {
        $idAnnonce = (int)($_POST['id_annonce'] ?? 0);
        try {
            $result = $this->api->post('/conversations', ['id_annonce' => $idAnnonce]);
            $id = $result['data']['id'] ?? 0;
            if ($id) {
                redirect('/messagerie/' . $id);
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/messagerie');
    }

    public function show($id)
    {
        return view('front.messagerie.show', [
            'id_conversation' => (int)$id,
            'title'           => 'Conversation - UpcycleConnect',
            'token'           => $_SESSION['user']['token'] ?? '',
            'user_id'         => $_SESSION['user']['id'] ?? 0,
        ]);
    }
}
