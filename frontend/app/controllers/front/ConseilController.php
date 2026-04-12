<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class ConseilController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        $conseils = [];
        $sujets   = [];

        try {
            $res      = $this->api->get('/conseils', ['categorie' => $_GET['categorie'] ?? '']);
            $conseils = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $conseils = [];
        }

        try {
            $res    = $this->api->get('/forum/sujets', ['categorie' => $_GET['categorie'] ?? '']);
            $sujets = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $sujets = [];
        }

        return view('front.conseils.index', [
            'title'    => 'Espace Conseils - UpcycleConnect',
            'conseils' => $conseils,
            'sujets'   => $sujets,
            'onglet'   => $_GET['onglet'] ?? 'conseils',
        ]);
    }

    public function showConseil($id)
    {
        $conseil = [];
        try {
            $res     = $this->api->get('/conseils/' . $id);
            $conseil = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $conseil = [];
        }

        return view('front.conseils.detail', [
            'title'   => ($conseil['titre'] ?? 'Conseil') . ' - UpcycleConnect',
            'conseil' => $conseil,
        ]);
    }

    public function showSujet($id)
    {
        $sujet = [];

        try {
            $res   = $this->api->get('/forum/sujets/' . $id);
            $sujet = $res['data'] ?? $res;
        } catch (\Exception $e) {
            $sujet = [];
        }

        return view('front.conseils.sujet', [
            'title' => ($sujet['titre'] ?? 'Sujet') . ' - Forum UpcycleConnect',
            'sujet' => $sujet,
        ]);
    }

    public function createSujet()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        return view('front.conseils.create_sujet', [
            'title' => 'Nouveau sujet - Forum UpcycleConnect',
        ]);
    }

    public function storeSujet()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        try {
            $result = $this->api->post('/forum/sujets', [
                'titre'          => $_POST['titre'] ?? '',
                'contenu'        => $_POST['contenu'] ?? '',
                'categorie'      => $_POST['categorie'] ?? 'general',
                'id_utilisateur' => $_SESSION['user']['id'] ?? 0,
            ]);
            redirect('/conseils/forum/' . ($result['data']['id'] ?? ''));
        } catch (\Exception $e) {
            return view('front.conseils.create_sujet', [
                'title' => 'Nouveau sujet - Forum UpcycleConnect',
                'error' => 'Une erreur est survenue. Veuillez réessayer.',
            ]);
        }
    }

    public function storeReponse($id)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        try {
            $this->api->post('/forum/sujets/' . $id . '/reponses', [
                'contenu'        => $_POST['contenu'] ?? '',
                'id_utilisateur' => $_SESSION['user']['id'] ?? 0,
            ]);
        } catch (\Exception $e) {}

        redirect('/conseils/forum/' . $id);
    }

    public function marquerSolution($idSujet, $idReponse)
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }

        try {
            $this->api->patch('/forum/sujets/' . $idSujet . '/reponses/' . $idReponse . '/solution');
        } catch (\Exception $e) {}

        redirect('/conseils/forum/' . $idSujet);
    }
}