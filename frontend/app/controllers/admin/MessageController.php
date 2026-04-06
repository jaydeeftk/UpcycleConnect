<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class MessageController
{
    private $api;
    public function __construct()
    {
        \App\Middleware\AdminMiddleware::check();
        $this->api = new ApiService();
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/messages');
            return view('admin.messages.index', ['messages' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.messages.index', ['messages' => [], 'error' => $e->getMessage()]);
        }
    }
}