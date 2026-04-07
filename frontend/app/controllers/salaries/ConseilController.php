<?php

namespace App\Controllers\salaries;

use App\Services\ApiService;

class ConseilController
{
    private $api;

    public function __construct()
    {
        $this->api = new ApiService();

       if (!isset($_SESSION['user'])) {
    redirect('/UpcycleConnect-PA2526/frontend/public/login');
}

        if (isset($_SESSION['token'])) {
            $this->api->setToken($_SESSION['token']);
        }
    }

    public function index()
    {
        try {
            $result = $this->api->get('/salaries/conseils');

            return view('salaries.conseils.index', [
                'conseils'      => $result['data'] ?? [],
                'page_title'    => 'Gestion des conseils',
                'page_subtitle' => 'Créez et gérez vos conseils publiés sur le site'
            ]);
        } catch (\Exception $e) {
            return view('salaries.conseils.index', [
                'error'         => $e->getMessage(),
                'conseils'      => [],
                'page_title'    => 'Gestion des conseils',
                'page_subtitle' => 'Créez et gérez vos conseils publiés sur le site'
            ]);
        }
    }

    public function store()
    {
        try {
            $this->api->post('/salaries/conseils/create', [
                'contenu'      => $_POST['contenu'] ?? '',
                'date_d_ajout' => date('Y-m-d H:i:s'),
                'id_salaries'  => $_SESSION['user']['id'] ?? 0
            ]);

            $_SESSION['success'] = 'Conseil ajouté avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/UpcycleConnect-PA2526/frontend/public/salaries/conseils');
    }

    public function edit($id)
    {
        try {
            $result = $this->api->get('/salaries/conseils/' . $id);

            return view('salaries.conseils.index', [
                'conseils'      => $this->api->get('/salaries/conseils')['data'] ?? [],
                'conseil_edit'  => $result['data'] ?? null,
                'page_title'    => 'Modifier un conseil',
                'page_subtitle' => 'Modifiez le contenu du conseil'
            ]);
        } catch (\Exception $e) {
            return view('salaries.conseils.index', [
                'error'         => $e->getMessage(),
                'conseils'      => [],
                'page_title'    => 'Gestion des conseils',
                'page_subtitle' => 'Créez et gérez vos conseils publiés sur le site'
            ]);
        }
    }

    public function update($id)
    {
        try {
            $this->api->put('/salaries/conseils/' . $id, [
                'contenu' => $_POST['contenu'] ?? ''
            ]);

            $_SESSION['success'] = 'Conseil modifié avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/UpcycleConnect-PA2526/frontend/public/salaries/conseils');
    }

    public function delete($id)
    {
        try {
            $this->api->delete('/salaries/conseils/' . $id);
            $_SESSION['success'] = 'Conseil supprimé avec succès.';
        } catch (\Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        redirect('/UpcycleConnect-PA2526/frontend/public/salaries/conseils');
    }
}