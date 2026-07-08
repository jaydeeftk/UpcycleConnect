<?php

namespace App\Controllers\Front;

use App\Services\ApiService;

class PrestationController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();
    }

    public function index()
    {
        $prestations = [];

        try {
            $prestations = $this->api->get('/services');
        } catch (\Exception $e) {
            $prestations = [];
        }

        return view('front.prestations.index', [
            'title' => 'Prestations - UpcycleConnect',
            'prestations' => $prestations
        ]);
    }

    public function show($id)
    {
        $prestation = [];

        try {
            $prestation = $this->api->get('/services/' . $id);
        } catch (\Exception $e) {
            $prestation = [];
        }

        return view('front.prestations.detail', [
            'title' => 'Détail prestation - UpcycleConnect',
            'prestation' => $prestation
        ]);
    }

    public function create()
    {
        return view('front.demandes.create', [
            'title' => 'Faire une demande - UpcycleConnect'
        ]);
    }

    public function store()
    {
        if (!isset($_SESSION['user'])) {
            redirect('/login');
            return;
        }
        $photoUrl = $this->stockerPhoto($_FILES['photo'] ?? null);
        if ($photoUrl === null) {
            redirect('/demande-prestation?photo=1');
            return;
        }
        $payload = [
            'nom_objet'    => $_POST['nom_objet'] ?? '',
            'categorie'    => $_POST['categorie'] ?? '',
            'type_objet'   => $_POST['type_objet'] ?? '',
            'etat'         => $_POST['etat'] ?? '',
            'description'  => $_POST['description'] ?? '',
            'localisation' => $_POST['localisation'] ?? '',
            'budget'       => $_POST['budget'] ?? '',
            'photo_url'    => $photoUrl,
        ];
        try {
            $this->api->post('/prestations/demandes', $payload);
            redirect('/mes-prestations?envoye=1');
        } catch (\Exception $e) {
            redirect('/mes-prestations?erreur=1');
        }
    }

    private function stockerPhoto($file): ?string
    {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($file['tmp_name'] ?? '') ?: '';
        if (!isset($allowed[$mime]) || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return null;
        }
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $dir = __DIR__ . '/../../../public/uploads/prestations/' . $uid;
        if (!is_dir($dir) && !mkdir($dir, 0o755, true)) {
            return null;
        }
        $name = bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
            return null;
        }
        return '/uploads/prestations/' . $uid . '/' . $name;
    }
}