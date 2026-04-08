<?php

$router->get('/', 'Front\HomeController@index');
$router->get('/home', 'Front\HomeController@index');
$router->get('/lang/{lang}', 'Front\LangController@switch');
$router->get('/prestations', 'Front\PrestationController@index');
$router->get('/prestations/{id}', 'Front\PrestationController@show');
$router->get('/demande-prestation', 'Front\PrestationController@create');
$router->post('/demande-prestation', 'Front\PrestationController@store');
$router->get('/catalogue/services', 'Front\CatalogueController@services');
$router->get('/services/{id}', 'Front\CatalogueController@showService');
$router->get('/catalogue/formations', 'Front\CatalogueController@formations');
$router->get('/formations/{id}', 'Front\CatalogueController@showFormation');
$router->post('/formations/{id}/inscrire', 'Front\CatalogueController@inscrireFormation');
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

$router->get('/annonces', 'Front\AnnonceController@index');
$router->get('/annonces/create', 'Front\AnnonceController@create');
$router->post('/annonces/store', 'Front\AnnonceController@store');
$router->get('/annonces/{id}', 'Front\AnnonceController@show');
$router->post('/annonces/{id}/annuler', 'Front\AnnonceController@annuler');

$router->get('/conteneurs/create', 'Front\ConteneurController@create');
$router->post('/conteneurs/store', 'Front\ConteneurController@store');

$router->get('/evenements', 'Front\EvenementController@index');
$router->get('/evenements/{id}', 'Front\EvenementController@show');
$router->post("/evenements/{id}/participer", "Front\\EvenementController@participer");
$router->post("/evenements/{id}/desinscrire", "Front\\EvenementController@desinscrire");
$router->post("/formations/{id}/desinscrire", "Front\\CatalogueController@desinscrireFormation");
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
$router->get('/paiement/success', 'Front\UserController@paiementSuccess');


$router->get('/admin-portal-access', 'Front\AuthController@showAdminGate');
$router->post('/admin-portal-access', 'Front\AuthController@adminLogin');

$router->group(['prefix' => 'admin'], function($router) {
    $router->get('/', 'Admin\DashboardController@index');
    $router->get('/dashboard', 'Admin\DashboardController@index');

    $router->get('/utilisateurs', 'Admin\UtilisateurController@index');
    $router->post('/utilisateurs/store', 'Admin\UtilisateurController@store');
    $router->get('/utilisateurs/{id}/delete/confirm', 'Admin\UtilisateurController@delete');
    $router->get('/utilisateurs/{id}/delete', 'Admin\UtilisateurController@confirmDelete');
    $router->get('/utilisateurs/{id}', 'Admin\UtilisateurController@show');

    $router->get('/annonces', 'Admin\AnnonceController@index');
    $router->post('/annonces/{id}/valider', 'Admin\AnnonceController@validate');
    $router->post('/annonces/{id}/refuser', 'Admin\AnnonceController@reject');
    $router->post('/annonces/{id}/supprimer', 'Admin\AnnonceController@delete');

    $router->get('/evenements', 'Admin\EvenementController@index');
    $router->get('/evenements/create', 'Admin\EvenementController@create');
    $router->post('/evenements/store', 'Admin\EvenementController@store');
    $router->get('/evenements/{id}/delete', 'Admin\EvenementController@delete');

    $router->get('/formations', 'Admin\FormationController@index');
    $router->post('/formations/store', 'Admin\FormationController@store');
    $router->get('/formations/{id}/delete', 'Admin\FormationController@delete');

    $router->get('/conteneurs', 'Admin\ConteneurController@index');
    $router->post('/conteneurs/store', 'Admin\ConteneurController@store');
    $router->get('/conteneurs/{id}/delete', 'Admin\ConteneurController@delete');

    $router->get('/categories', 'Admin\CategorieController@index');
    $router->post('/categories/store', 'Admin\CategorieController@store');
    $router->get('/categories/{id}/delete', 'Admin\CategorieController@delete');

    $router->get('/contrats', 'Admin\ContratController@index');
    $router->get('/contrats/{id}/delete', 'Admin\ContratController@delete');
    $router->get('/factures', 'Admin\FactureController@index');

    $router->get('/notifications', 'Admin\NotificationController@index');
    $router->post('/notifications/store', 'Admin\NotificationController@store');
    $router->get('/notifications/{id}/delete', 'Admin\NotificationController@delete');
    $router->get('/messages', 'Admin\MessageController@index');

    $router->get('/parametres', 'Admin\ParametreController@index');
    $router->post('/parametres/update', 'Admin\ParametreController@update');
    $router->post('/parametres/update-maintenance', 'Admin\ParametreController@updateMaintenance');

    $router->get('/demandes', 'Admin\DemandeController@index');
    $router->post('/demandes/valider/([0-9]+)', 'Admin\DemandeController@valider');
    $router->post('/demandes/refuser/([0-9]+)', 'Admin\DemandeController@refuser');

    
});