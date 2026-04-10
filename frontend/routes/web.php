<?php

$router->get('/', 'Front\HomeController@index');
$router->get('/home', 'Front\HomeController@index');

$router->get('/prestations', 'Front\PrestationController@index');
$router->get('/prestations/{id}', 'Front\PrestationController@show');
$router->get('/demande-prestation', 'Front\PrestationController@create');
$router->post('/demande-prestation', 'Front\PrestationController@store');

$router->get('/catalogue/services', 'Front\CatalogueController@services');
$router->get('/catalogue/formations', 'Front\CatalogueController@formations');
$router->get('/catalogue/evenements', 'Front\CatalogueController@evenements');

$router->get('/conseils', 'Front\ConseilController@index');
$router->get('/conseils/forum/create', 'Front\ConseilController@createSujet');
$router->post('/conseils/forum/store', 'Front\ConseilController@storeSujet');
$router->get('/conseils/forum/{id}', 'Front\ConseilController@showSujet');
$router->post('/conseils/forum/{id}/repondre', 'Front\ConseilController@storeReponse');
$router->post('/conseils/forum/{idSujet}/solution/{idReponse}', 'Front\ConseilController@marquerSolution');
$router->get('/conseils/{id}', 'Front\ConseilController@showConseil');
$router->get('/score', 'Front\ScoreController@index');
$router->get('/planning', 'Front\PlanningController@index');

$router->get('/annonces/create', 'Front\AnnonceController@create');
$router->post('/annonces/store', 'Front\AnnonceController@store');

$router->get('/conteneurs/create', 'Front\ConteneurController@create');
$router->post('/conteneurs/store', 'Front\ConteneurController@store');

$router->get('/evenements', 'Front\EvenementController@index');
$router->get('/evenements/{id}', 'Front\EvenementController@show');

$router->get('/a-propos', 'Front\PageController@apropos');
$router->get('/contact', 'Front\PageController@contact');

$router->get('/login', 'Front\AuthController@showLogin');
$router->post('/login', 'Front\AuthController@login');
$router->get('/register', 'Front\AuthController@showRegister');
$router->post('/register', 'Front\AuthController@register');
$router->get('/logout', 'Front\AuthController@logout');
$router->get('/tutoriel/done', 'Front\UserController@tutorielDone');

$router->get('/mes-demandes', 'Front\UserController@mesDemandes');
$router->get('/mes-prestations', 'Front\UserController@mesPrestations');
$router->get('/paiements', 'Front\UserController@paiements');
$router->get('/payer', 'Front\UserController@payer');

$router->get('/admin-portal-access', 'Admin\\PortalController@show');
$router->post('/admin-portal-access', 'Admin\\PortalController@login');

$router->group(['prefix' => 'admin'], function($router) {

    $router->get('/', 'Admin\DashboardController@index');
    $router->get('/dashboard', 'Admin\DashboardController@index');
    $router->get('/categories', 'Admin\CategorieController@index');
    $router->post('/categories/store', 'Admin\CategorieController@store');
    $router->get('/categories/{id}/delete', 'Admin\CategorieController@delete');
    $router->get('/messages', 'Admin\MessageController@index');
    $router->get('/parametres', 'Admin\ParametreController@index');
    $router->get('/annonces', 'Admin\AnnonceController@index');
    $router->get('/annonces/{id}/validate', 'Admin\AnnonceController@validate');
    $router->get('/annonces/{id}/reject', 'Admin\AnnonceController@reject');
    $router->get('/evenements', 'Admin\EvenementController@index');
    $router->get('/evenements/create', 'Admin\EvenementController@create');
    $router->post('/evenements/store', 'Admin\EvenementController@store');
    $router->get('/evenements/{id}/delete', 'Admin\EvenementController@delete');

    $router->get('/utilisateurs', 'Admin\UtilisateurController@index');
    $router->post('/utilisateurs/store', 'Admin\UtilisateurController@store');
    $router->get('/utilisateurs/{id}', 'Admin\UtilisateurController@show');
    $router->get('/utilisateurs/{id}/delete', 'Admin\UtilisateurController@confirmDelete');
    $router->get('/utilisateurs/{id}/delete/confirm', 'Admin\UtilisateurController@delete');

    $router->get('/conteneurs', 'Admin\\ConteneurController@index');
    $router->get('/conteneurs/{id}/accept', 'Admin\\ConteneurController@accept');
    $router->get('/conteneurs/{id}/refuse', 'Admin\\ConteneurController@refuse');
    $router->get('/conteneurs/{id}/delete', 'Admin\\ConteneurController@delete');
    $router->post('/maintenance/toggle', 'Admin\\PortalController@toggleMaintenance');

});