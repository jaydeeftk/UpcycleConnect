<?php
namespace App\Controllers\Front;

use App\Services\ApiService;

class ScoreController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
        if (!empty($_SESSION['user']['token'])) {
            $this->api->setToken($_SESSION['user']['token']);
        }
    }

    public function index()
    {
        $score = 0;
        $historique = [];

        if (!empty($_SESSION['user']['id'])) {
            try {
                $result = $this->api->get('/score/' . $_SESSION['user']['id']);
                $data = isset($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
                $score      = $data['score']      ?? 0;
                $historique = $data['historique']  ?? [];
            } catch (\Exception $e) {}
        }

        return view('front.score.index', [
            'score'      => $score,
            'historique' => $historique,
            'page_title' => 'Mon Score Écologique',
        ]);
    }
}
