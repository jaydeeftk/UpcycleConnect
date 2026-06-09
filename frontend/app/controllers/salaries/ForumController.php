<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class ForumController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();

        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        if (isset($_SESSION['token'])) {
            $this->api->setToken($_SESSION['token']);
        }
    }

    public function index()
    {
        try {
            $result = $this->api->get('/salaries/forum');
            $sujets = $result['data'] ?? (is_array($result) && !isset($result['success']) ? $result : []);

            return view('salaries.forum.index', [
                'sujets'        => $sujets,
                'page_title'    => 'Modération du forum',
                'page_subtitle' => 'Surveillez et modérez les discussions de la communauté',
            ]);
        } catch (\Exception $e) {
            return view('salaries.forum.index', [
                'error'         => $e->getMessage(),
                'sujets'        => [],
                'page_title'    => 'Modération du forum',
                'page_subtitle' => 'Surveillez et modérez les discussions de la communauté',
            ]);
        }
    }

    public function deleteSujet($id)
    {
        try {
            $this->api->delete('/salaries/forum/sujets/' . $id);
            $_SESSION['success'] = t('sal_flash_sujet_deleted', 'Sujet supprimé.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/salaries/forum');
    }

    public function deleteReponse($id)
    {
        try {
            $this->api->delete('/salaries/forum/reponses/' . $id);
            $_SESSION['success'] = t('sal_flash_reponse_deleted', 'Réponse supprimée.');
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/salaries/forum');
    }
}
