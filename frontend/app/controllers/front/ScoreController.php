<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class ScoreController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        $score      = 0;
        $historique = [];

        try {
            $res        = $this->api->get('/score/' . $_SESSION['user']['id']);
            $res        = $res['data'] ?? $res;
            $score      = $res['score']      ?? 0;
            $historique = $res['historique'] ?? [];
        } catch (\Exception $e) {
            $score      = 0;
            $historique = [];
        }

        return view('front.score.index', [
            'title'      => 'Mon Upcycling Score - UpcycleConnect',
            'score'      => $score,
            'historique' => $historique,
        ]);
    }
}