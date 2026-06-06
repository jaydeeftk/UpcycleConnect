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
        $data = [];
        if (!empty($_SESSION['user']['id'])) {
            try {
                $result = $this->api->get('/score/' . $_SESSION['user']['id']);
                $data = isset($result['data']) ? $result['data'] : (is_array($result) && !isset($result['success']) ? $result : []);
            } catch (\Exception $e) {}
        }

        return view('front.score.index', [
            'score'               => $data['score']               ?? 0,
            'score_max'           => $data['score_max']           ?? 1000,
            'pct'                 => $data['pct']                  ?? 0,
            'historique'          => $data['historique']          ?? [],
            'badge_actuel'        => $data['badge_actuel']        ?? null,
            'badge_suivant'       => $data['badge_suivant']       ?? null,
            'points_vers_suivant' => $data['points_vers_suivant'] ?? 0,
            'badges'              => $data['badges']              ?? [],
            'page_title'          => 'Mon Score Écologique',
        ]);
    }
}
