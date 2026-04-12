<?php
namespace App\Controllers\Front;

class LangController
{
    public function switch($lang)
    {
        $allowed = ['fr', 'en', 'es', 'de'];
        if (in_array($lang, $allowed)) {
            $_SESSION['lang'] = $lang;
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        header('Location: ' . $referer);
        exit;
    }
}
