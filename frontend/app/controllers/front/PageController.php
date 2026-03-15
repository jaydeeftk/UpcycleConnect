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

    public function contact()
    {
        return view('front.contact.index', [
            'title' => 'Contact - UpcycleConnect'
        ]);
    }
}