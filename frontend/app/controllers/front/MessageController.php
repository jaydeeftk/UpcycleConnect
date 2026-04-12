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
        $userId = $_SESSION['user']['id'] ?? 0;
        $messages = [];
        try {
            $result = $this->api->get('/messages/user/' . $userId);
            $messages = $result['data'] ?? (is_array($result) ? $result : []);
        } catch (\Exception $e) {}

        return view('front.messages.index', [
            'messages' => $messages,
            'page_title' => 'Mes messages',
            'token' => $_SESSION['user']['token'] ?? '',
            'user_id' => $_SESSION['user']['id'] ?? 0,
        ]);
    }
}
