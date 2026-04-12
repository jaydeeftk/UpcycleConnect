<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class NotificationController
{
    private $api;

    public function __construct()
    {
        \App\Middleware\AdminMiddleware::check();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? '');
    }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/notifications');
            $notifications = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) { $notifications = []; }
        return view('admin.notifications.index', ['notifications' => $notifications, 'page_title' => 'Notifications']);
    }

    public function store()
    {
        try {
            $this->api->post('/admin/notifications', [
                'contenu'            => $_POST['contenu'] ?? '',
                'id_administrateurs' => (int)($_SESSION['user']['id'] ?? 1),
                'id_utilisateurs'    => (int)($_POST['id_utilisateurs'] ?? 0),
            ]);
        } catch (\Exception $e) {}
        redirect('/admin/notifications');
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/notifications/' . $id); } catch (\Exception $e) {}
        redirect('/admin/notifications');
    }
}