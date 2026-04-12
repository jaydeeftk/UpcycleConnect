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
            $messages = isset($result['data']) && is_array($result['data']) 
                ? $result['data'] 
                : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) { 
            $messages = []; 
        }

        return view('admin.messages.index', [
            'messages' => $messages, 
            'page_title' => 'Messages'
        ]);
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/admin/messages/' . $id);
        } catch (\Exception $e) {}
        header('Location: /admin/messages');
        exit;
    }
}