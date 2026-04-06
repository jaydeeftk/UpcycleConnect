<?php
$router->get('/', 'Front\HomeController@index');
$router->get('/home', 'Front\HomeController@index');
$router->get('/login', 'Front\AuthController@showLogin');
$router->post('/login', 'Front\AuthController@login');
$router->get('/register', 'Front\AuthController@showRegister');
$router->post('/register', 'Front\AuthController@register');
$router->get('/logout', 'Front\AuthController@logout');
$router->get('/tutoriel/done', 'Front\UserController@tutorielDone');

$router->get('/prestations', 'Front\PrestationController@index');
$router->get('/conseils', 'Front\ConseilController@index');
$router->get('/conseils/forum/create', 'Front\ConseilController@createSujet');
$router->get('/conseils/forum/{id}', 'Front\ConseilController@showSujet');
$router->get('/annonces/create', 'Front\AnnonceController@create');
$router->post('/annonces/store', 'Front\AnnonceController@store');

$router->group(['prefix' => 'admin'], function($router) {
    $router->get('/dashboard', 'Admin\DashboardController@index');
    $router->get('/utilisateurs', 'Admin\UtilisateurController@index');
    $router->get('/annonces', 'Admin\AnnonceController@index');
    $router->get('/demandes', 'Admin\DemandeController@index');
    $router->post('/demandes/valider/([0-9]+)', 'Admin\DemandeController@valider');
    $router->post('/demandes/refuser/([0-9]+)', 'Admin\DemandeController@refuser');
});