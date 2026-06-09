<?php
/**
 * Protection CSRF (Cross-Site Request Forgery) pour les requetes mutantes (POST).
 *
 * - csrf_token()  : jeton stable par session (cree a la volee).
 * - csrf_field()  : champ cache a inserer dans chaque <form method="POST">.
 * - csrf_verify() : valide le jeton recu (corps POST OU en-tete X-CSRF-Token pour le JS).
 *
 * La validation est appliquee dans le routeur (public/index.php) sur toute requete POST.
 * Les requetes JS (fetch/XHR) sont couvertes par un patch global dans les layouts qui
 * ajoute automatiquement l'en-tete X-CSRF-Token aux requetes mutantes same-origin.
 */

if (!function_exists('csrf_token')) {

    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
    }

    function csrf_verify(): bool
    {
        $real = $_SESSION['csrf_token'] ?? '';
        if ($real === '') {
            return false;
        }
        $sent = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return is_string($sent) && $sent !== '' && hash_equals($real, $sent);
    }
}
