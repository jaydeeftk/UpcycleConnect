<?php

namespace App\Controllers\Admin;

use App\Services\ApiService;

class MessageController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/messages');

            return view('admin.messages.index', [
                'messages' => $result['data'] ?? [],
                'page_title' => 'Gestion des messages'
            ]);
        } catch (\Exception $e) {
            return view('admin.messages.index', [
                'error' => $e->getMessage(),
                'messages' => [],
                'page_title' => 'Gestion des messages'
            ]);
        }
    }
}