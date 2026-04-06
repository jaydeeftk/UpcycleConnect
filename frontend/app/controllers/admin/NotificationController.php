<?php
namespace App\Controllers\Admin;
use App\Services\ApiService;

class NotificationController
{
    private $api;
    public function __construct() { $this->api = new ApiService(); }

    public function index()
    {
        try {
            $result = $this->api->get('/admin/notifications');
            return view('admin.notifications.index', ['notifications' => $result['data'] ?? []]);
        } catch (\Exception $e) {
            return view('admin.notifications.index', ['notifications' => [], 'error' => $e->getMessage()]);
        }
    }

    public function delete($id)
    {
        try { $this->api->delete('/admin/notifications/' . $id); } catch (\Exception $e) {}
        redirect('/UpcycleConnect-PA2526/frontend/public/admin/notifications');
    }
}