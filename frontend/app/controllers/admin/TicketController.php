<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class TicketController
{
    private $api;

    public function __construct()
    {
        \App\Middleware\AdminMiddleware::check();
        $this->api = new ApiService();
        $this->api->setToken($_SESSION['user']['token'] ?? ($_SESSION['token'] ?? ''));
    }

    public function index()
    {
        $tickets = [];
        try {
            $result = $this->api->get('/admin/tickets');
            $tickets = isset($result['data']) && is_array($result['data'])
                ? $result['data']
                : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {}

        return view('admin.tickets.index', [
            'tickets'    => $tickets,
            'page_title' => 'Tickets support',
            'token'      => $_SESSION['user']['token'] ?? ($_SESSION['token'] ?? ''),
            'user_id'    => $_SESSION['user']['id'] ?? 0,
        ]);
    }

    public function accepter($id)
    {
        try {
            $this->api->post('/admin/tickets/' . (int)$id . '/accepter', []);
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/admin/tickets');
    }

    public function show($id)
    {
        return view('admin.tickets.show', [
            'id_ticket'  => (int)$id,
            'page_title' => 'Ticket #' . (int)$id,
            'token'      => $_SESSION['user']['token'] ?? ($_SESSION['token'] ?? ''),
            'user_id'    => $_SESSION['user']['id'] ?? 0,
        ]);
    }
}
