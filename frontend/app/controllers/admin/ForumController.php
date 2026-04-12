<?php
namespace App\Controllers\Admin;

use App\Services\ApiService;

class ForumController
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
            $result = $this->api->get('/admin/forum');
            $sujets = isset($result['data']) && is_array($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
        } catch (\Exception $e) {
            $sujets = [];
        }

        return view('admin.forum.index', [
            'sujets'        => $sujets,
            'page_title'    => 'Modération Forum',
            'page_subtitle' => 'Gérez les sujets et réponses du forum communautaire',
        ]);
    }

    public function deleteSujet($id)
    {
        try { $this->api->delete('/admin/forum/sujets/' . $id); } catch (\Exception $e) {}
        header('Location: /admin/forum');
        exit;
    }

    public function deleteReponse($id)
    {
        try { $this->api->delete('/admin/forum/reponses/' . $id); } catch (\Exception $e) {}
        header('Location: /admin/forum');
        exit;
    }
}
