<?php

namespace App\Controllers\Front;

class PageController
{
    public function apropos()
    {
        return view('front.apropos.index', [
            'title' => 'À propos - UpcycleConnect'
        ]);
    }

    public function mentionsLegales()
    {
        return view('front.legal.mentions', [
            'title' => 'Mentions légales - UpcycleConnect'
        ]);
    }

    public function confidentialite()
    {
        return view('front.legal.confidentialite', [
            'title' => 'Politique de confidentialité - UpcycleConnect'
        ]);
    }
}