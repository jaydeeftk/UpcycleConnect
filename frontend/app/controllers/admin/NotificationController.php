<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;
class NotificationController
{
    private $api;
    public function __construct()
    {
        \App\Middleware\AdminMiddleware::handle();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }
    public function index()
    {
        try {
            $result = $this->api->get('/admin/notifications');
            $notifications = $result['data'] ?? [];
        } catch (\Exception $e) { $notifications = []; }
        return view('admin.notifications.index', ['notifications' => $notifications, 'page_title' => 'Notifications']);
    }
    public function send()
    {
        try {
            $this->api->post('/admin/notifications/send', [
                'titre'   => $_POST['titre'] ?? '',
                'message' => $_POST['message'] ?? '',
                'cible'   => $_POST['cible'] ?? 'all',
            ]);
        } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/notifications');
    }
}