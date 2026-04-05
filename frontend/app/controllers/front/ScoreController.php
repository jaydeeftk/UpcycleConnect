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
        // if (!isset($_SESSION['user'])) {
        //     redirect('/UpcycleConnect-PA2526/frontend/public/login');
        // }

        $score      = 0;
        $historique = [];

        try {
            $data       = $this->api->get('/score/' . $_SESSION['user']['id']);
            $score      = $data['score']      ?? 0;
            $historique = $data['historique'] ?? [];
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