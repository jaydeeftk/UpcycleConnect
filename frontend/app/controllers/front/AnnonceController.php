<?php
namespace App\Controllers\Front;
use App\Services\ApiService;
class AnnonceController
{
    private $api;
    public function __construct()
    {
        $this->api = new ApiService();
    }
    public function create()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        return view('front.annonces.create', [
            'title' => 'Déposer une annonce - UpcycleConnect',
        ]);
    }
    public function store()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
        }
        $data = [
            'titre'        => $_POST['titre'] ?? '',
            'categorie'    => $_POST['categorie'] ?? '',
            'description'  => $_POST['description'] ?? '',
            'etat'         => $_POST['etat'] ?? '',
            'type_annonce' => $_POST['type_annonce'] ?? 'don',
            'prix'         => $_POST['type_annonce'] === 'vente' ? (float)($_POST['prix'] ?? 0) : 0,
            'ville'        => $_POST['ville'] ?? '',
            'code_postal'  => $_POST['code_postal'] ?? '',
            'user_id'      => $_SESSION['user']['id'] ?? 0,
        ];
        error_log("SESSION USER: " . json_encode($_SESSION['user']));
        try {
            $this->api->post('/annonces/create', $data);
            return view('front.annonces.create', [
                'title'   => 'Déposer une annonce - UpcycleConnect',
                'success' => 'Votre annonce a bien été soumise ! Elle sera vérifiée par notre équipe avant publication.',
            ]);
        } catch (\Exception $e) {
    return view('front.annonces.create', [
        'title' => 'Déposer une annonce - UpcycleConnect',
        'error' => $e->getMessage(),
    ]);
}
    }
}