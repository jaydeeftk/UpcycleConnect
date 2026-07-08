<?php

$router->get('/', 'Front\HomeController@index');
$router->get('/home', 'Front\HomeController@index');
$router->get('/lang/{lang}', 'Front\LangController@switch');

$router->get('/prestations', 'Front\PrestationController@index');
$router->get('/prestations/{id}', 'Front\PrestationController@show');
$router->get('/demande-prestation', 'Front\PrestationController@create');
$router->post('/demande-prestation', 'Front\PrestationController@store');

$router->get('/catalogue/services', 'Front\CatalogueController@services');
$router->post('/services/commande/photo', 'Front\CatalogueController@uploadCommandePhoto');
$router->get('/services/{id}', 'Front\CatalogueController@showService');
$router->get('/catalogue/formations', 'Front\CatalogueController@formations');
$router->get('/formations/{id}', 'Front\CatalogueController@showFormation');
$router->post('/formations/{id}/inscrire', 'Front\CatalogueController@inscrireFormation');
$router->post('/formations/{id}/desinscrire', 'Front\CatalogueController@desinscrireFormation');
$router->get('/catalogue/evenements', 'Front\CatalogueController@evenements');

$router->get('/conseils', 'Front\ConseilController@index');
$router->get('/conseils/forum/create', 'Front\ConseilController@createSujet');
$router->post('/conseils/forum/store', 'Front\ConseilController@storeSujet');
$router->get('/conseils/forum/{id}', 'Front\ConseilController@showSujet');
$router->post('/conseils/forum/{id}/repondre', 'Front\ConseilController@storeReponse');
$router->post('/conseils/forum/{idSujet}/solution/{idReponse}', 'Front\ConseilController@marquerSolution');
$router->post('/conseils/forum/reponses/{id}/supprimer', 'Front\ConseilController@deleteReponse');
$router->get('/conseils/{id}', 'Front\ConseilController@showConseil');

$router->get('/score', 'Front\ScoreController@index');
$router->get('/score/classement', 'Front\ScoreController@classement');
$router->get('/planning', 'Front\PlanningController@index');
$router->post('/planning/ajouter', 'Front\PlanningController@ajouter');
$router->post('/planning/{id}/supprimer', 'Front\PlanningController@supprimer');

$router->get('/annonces', 'Front\AnnonceController@index');
$router->get('/annonces/create', 'Front\AnnonceController@create');
$router->post('/annonces/store', 'Front\AnnonceController@store');
$router->get('/mes-annonces', 'Front\AnnonceController@mesAnnonces');
$router->get('/mes-objets', 'Front\AnnonceController@recuperation');
$router->post('/mes-objets/{id}/recuperer', 'Front\AnnonceController@recupererObjet');
$router->post('/mes-objets/recuperer-par-code', 'Front\AnnonceController@recupererParCode');
$router->get('/annonces/{id}', 'Front\AnnonceController@show');
$router->post('/annonces/{id}/annuler', 'Front\AnnonceController@annuler');
$router->post('/annonces/{id}/reserver', 'Front\AnnonceController@reserver');

$router->get('/conteneurs/create', 'Front\ConteneurController@create');
$router->post('/conteneurs/store', 'Front\ConteneurController@store');

$router->get('/evenements', 'Front\EvenementController@index');
$router->get('/evenements/{id}', 'Front\EvenementController@show');
$router->post('/evenements/{id}/participer', 'Front\EvenementController@participer');
$router->post('/evenements/{id}/desinscrire', 'Front\EvenementController@desinscrire');

$router->get('/historique', 'Front\UserController@historique');
$router->get('/contact', 'Front\UserController@contact');
$router->post('/contact/send', 'Front\UserController@sendContact');
$router->post('/professionnels/favoris/{id}/toggle', 'Front\AnnonceController@toggleFavori');

$router->get('/a-propos', 'Front\PageController@apropos');
$router->get('/mentions-legales', 'Front\PageController@mentionsLegales');
$router->get('/confidentialite', 'Front\PageController@confidentialite');

$router->get('/login', 'Front\AuthController@showLogin');
$router->post('/login', 'Front\AuthController@login');
$router->get('/register', 'Front\AuthController@showRegister');
$router->post('/register', 'Front\AuthController@register');
$router->get('/mot-de-passe-oublie', 'Front\AuthController@showForgotPassword');
$router->post('/mot-de-passe-oublie', 'Front\AuthController@forgotPassword');
$router->get('/reset-password', 'Front\AuthController@showResetPassword');
$router->post('/reset-password', 'Front\AuthController@resetPassword');
$router->get('/siret/verify/{siret}', 'Front\AuthController@verifySiret');
$router->get('/verify', 'Front\AuthController@verifyEmail');
$router->get('/logout', 'Front\AuthController@logout');
$router->get('/tutoriel/done', 'Front\UserController@tutorielDone');

$router->get('/messages', 'Front\MessageController@index');
$router->get('/messages/historique', 'Front\MessageController@historique');
$router->get('/messages/historique/{id}', 'Front\MessageController@historiqueDetail');

$router->get('/messagerie', 'Front\MessagerieController@index');
$router->post('/messagerie/demarrer', 'Front\MessagerieController@demarrer');
$router->get('/messagerie/{id}', 'Front\MessagerieController@show');
$router->post('/messagerie/{id}/supprimer', 'Front\MessagerieController@supprimer');
$router->get('/notifications', 'Front\UserController@notifications');
$router->post('/notifications/{id}/lu', 'Front\UserController@notificationLue');
$router->get('/mes-demandes', 'Front\UserController@mesDemandes');
$router->get('/mes-prestations', 'Front\UserController@mesPrestations');
$router->post('/mes-prestations/{id}/annuler', 'Front\UserController@annulerDemandePrestation');
$router->get('/paiements', 'Front\UserController@paiements');
$router->get('/payer', 'Front\UserController@payer');
$router->get('/paiement/success', 'Front\UserController@paiementSuccess');
$router->get('/factures/{id}/pdf', 'Front\UserController@facturePdf');
$router->post('/remboursements/demande', 'Front\UserController@demandeRemboursement');

$router->get('/professionnel', 'Front\ProfessionnelController@dashboard');
$router->get('/professionnel/projets/create', 'Front\ProfessionnelController@createProjet');
$router->post('/professionnel/projets/store', 'Front\ProfessionnelController@storeProjet');
$router->get('/professionnel/projets/{id}', 'Front\ProfessionnelController@showProjet');
$router->post('/professionnel/projets/{id}/update', 'Front\ProfessionnelController@updateProjet');
$router->post('/professionnel/projets/{id}/etapes', 'Front\ProfessionnelController@ajouterEtape');
$router->post('/professionnel/projets/{idProjet}/etapes/{idEtape}/delete', 'Front\ProfessionnelController@supprimerEtape');
$router->post('/professionnel/projets/{id}/delete', 'Front\ProfessionnelController@deleteProjet');
$router->post('/professionnel/projets/{id}/suspendre', 'Front\ProfessionnelController@suspendreProjet');
$router->post('/professionnel/projets/{id}/reprendre', 'Front\ProfessionnelController@reprendreProjet');
$router->post('/professionnel/projets/{id}/terminer', 'Front\ProfessionnelController@terminerProjet');
$router->post('/professionnel/projets/{id}/rouvrir', 'Front\ProfessionnelController@rouvrirProjet');
$router->post('/professionnel/favoris/{id}/remove', 'Front\ProfessionnelController@removeFavori');
$router->get('/professionnel/recuperation', 'Front\ProfessionnelController@recuperation');
$router->post('/professionnel/objets/scanner', 'Front\ProfessionnelController@scannerCode');
$router->post('/professionnel/objets/{id}/reserver', 'Front\ProfessionnelController@reserverObjet');
$router->post('/professionnel/objets/{id}/recuperer', 'Front\ProfessionnelController@recupererObjet');
$router->post('/professionnel/objets/{id}/annuler', 'Front\ProfessionnelController@annulerObjet');
$router->get('/professionnel/contrats/{id}/pdf', 'Front\ProfessionnelController@contratPdf');
$router->post('/professionnel/contrats/{id}/resilier', 'Front\ProfessionnelController@resilierContrat');
$router->post('/professionnel/notifications/{id}/lu', 'Front\ProfessionnelController@notificationLue');
$router->get('/professionnel/impact/pdf', 'Front\ProfessionnelController@impactPdf');

$router->get('/professionnel/annonces', 'Front\ProfessionnelController@annonces');
$router->get('/professionnel/annonces/create', 'Front\ProfessionnelController@createAnnonce');
$router->post('/professionnel/annonces/store', 'Front\ProfessionnelController@storeAnnonce');
$router->post('/professionnel/annonces/{id}/annuler', 'Front\ProfessionnelController@annulerAnnonce');

$router->get('/professionnel/abonnement', 'Front\ProfessionnelController@abonnement');
$router->post('/professionnel/abonnement/resilier', 'Front\ProfessionnelController@resilierAbonnement');

$router->get('/professionnel/publicites', 'Front\ProfessionnelController@publicites');
$router->post('/professionnel/publicites/{id}/annuler', 'Front\ProfessionnelController@annulerPublicite');
$router->get('/professionnel/commissions', 'Front\ProfessionnelController@commissions');
$router->get('/professionnel/services', 'Front\ProfessionnelController@services');
$router->post('/professionnel/services', 'Front\ProfessionnelController@creerService');
$router->post('/professionnel/services/{id}/supprimer', 'Front\ProfessionnelController@supprimerService');

$router->get('/professionnel/prestations', 'Front\ProfessionnelController@prestations');
$router->post('/professionnel/prestations/devis', 'Front\ProfessionnelController@proposerDevis');
$router->post('/professionnel/prestations/devis/{id}/retirer', 'Front\ProfessionnelController@retirerDevis');

$router->get('/admin-portal-access', 'Front\AuthController@showAdminGate');
$router->post('/admin-portal-access', 'Front\AuthController@adminLogin');

$router->group(['prefix' => 'admin'], function($router) {
    $router->get('/', 'Admin\DashboardController@index');
    $router->get('/dashboard', 'Admin\DashboardController@index');

    $router->get('/utilisateurs', 'Admin\UtilisateurController@index');
    $router->get('/utilisateurs/create', 'Admin\UtilisateurController@create');
    $router->post('/utilisateurs/store', 'Admin\UtilisateurController@store');
    $router->get('/utilisateurs/{id}', 'Admin\UtilisateurController@show');
    $router->post('/utilisateurs/{id}/update', 'Admin\UtilisateurController@update');
    $router->get('/utilisateurs/{id}/delete', 'Admin\UtilisateurController@confirmDelete');
    $router->post('/utilisateurs/{id}/delete/confirm', 'Admin\UtilisateurController@delete');
    $router->post('/utilisateurs/{id}/statut', 'Admin\UtilisateurController@statut');
    $router->post('/utilisateurs/{id}/role', 'Admin\UtilisateurController@role');

    $router->get('/annonces', 'Admin\AnnonceController@index');
    $router->post('/annonces/{id}/valider', 'Admin\AnnonceController@validate');
    $router->post('/annonces/{id}/refuser', 'Admin\AnnonceController@reject');
    $router->post('/annonces/{id}/supprimer', 'Admin\AnnonceController@delete');

    $router->get('/evenements', 'Admin\EvenementController@index');
    $router->get('/evenements/create', 'Admin\EvenementController@create');
    $router->post('/evenements/store', 'Admin\EvenementController@store');
    $router->post('/evenements/{id}/valider', 'Admin\EvenementController@valider');
    $router->post('/evenements/{id}/rejeter', 'Admin\EvenementController@rejeter');
    $router->post('/evenements/{id}/delete', 'Admin\EvenementController@delete');

    $router->get('/formations', 'Admin\FormationController@index');
    $router->post('/formations/store', 'Admin\FormationController@store');
    $router->post('/formations/{id}/valider', 'Admin\FormationController@valider');
    $router->post('/formations/{id}/rejeter', 'Admin\FormationController@rejeter');
    $router->post('/formations/{id}/delete', 'Admin\FormationController@delete');

    $router->get('/conteneurs', 'Admin\ConteneurController@index');
    $router->get('/conteneurs/{id}', 'Admin\ConteneurController@show');
    $router->post('/conteneurs/store', 'Admin\ConteneurController@store');
    $router->post('/conteneurs/{id}/update', 'Admin\ConteneurController@update');
    $router->post('/conteneurs/{id}/accept', 'Admin\ConteneurController@accept');
    $router->post('/conteneurs/{id}/refuse', 'Admin\ConteneurController@refuse');
    $router->post('/conteneurs/{id}/delete', 'Admin\ConteneurController@delete');

    $router->post('/box/{id}/dimensions', 'Admin\ConteneurController@updateBoxDimensions');
    $router->get('/categories', 'Admin\CategorieController@index');
    $router->post('/categories/store', 'Admin\CategorieController@store');
    $router->post('/categories/{id}/delete', 'Admin\CategorieController@delete');

    $router->get('/contrats', 'Admin\ContratController@index');
    $router->post('/contrats/store', 'Admin\ContratController@store');
    $router->post('/contrats/{id}/supprimer', 'Admin\ContratController@delete');

    $router->get('/abonnements', 'Admin\AbonnementController@index');

    $router->get('/publicites', 'Admin\PubliciteController@index');
    $router->post('/publicites/{id}/annuler', 'Admin\PubliciteController@annuler');

    $router->get('/factures', 'Admin\FactureController@index');
    $router->get('/factures/{id}/pdf', 'Admin\FactureController@pdf');
    $router->get('/notifications', 'Admin\NotificationController@index');
    $router->post('/notifications/store', 'Admin\NotificationController@store');
    $router->post('/notifications/{id}/delete', 'Admin\NotificationController@delete');
    $router->get('/tickets', 'Admin\TicketController@index');
    $router->post('/tickets/{id}/accepter', 'Admin\TicketController@accepter');
    $router->get('/tickets/{id}', 'Admin\TicketController@show');

    $router->get('/parametres', 'Admin\ParametreController@index');
    $router->post('/parametres/update', 'Admin\ParametreController@update');
    $router->post('/parametres/update-maintenance', 'Admin\ParametreController@updateMaintenance');

    $router->get('/demandes', 'Admin\DemandeController@index');
    $router->post('/demandes/valider/{id}', 'Admin\DemandeController@valider');
    $router->post('/demandes/refuser/{id}', 'Admin\DemandeController@refuser');

    $router->get('/finances', 'Admin\FinancesController@index');
    $router->get('/commissions', 'Admin\FinancesController@commissions');
    $router->get('/forum', 'Admin\ForumController@index');

    $router->get('/conseils', 'Admin\ConseilController@index');
    $router->post('/conseils/{id}/valider', 'Admin\ConseilController@valider');
    $router->post('/conseils/{id}/rejeter', 'Admin\ConseilController@rejeter');
    $router->post('/conseils/{id}/delete', 'Admin\ConseilController@delete');

    $router->get('/services', 'Admin\ServicesController@index');
    $router->post('/services/store', 'Admin\ServicesController@store');
    $router->post('/services/{id}/delete', 'Admin\ServicesController@delete');

    $router->get('/planning', 'Admin\PlanningController@index');

    $router->post('/forum/sujets/{id}/supprimer', 'Admin\ForumController@deleteSujet');
    $router->post('/forum/reponses/{id}/supprimer', 'Admin\ForumController@deleteReponse');

    
});

$router->group(['prefix' => 'salaries'], function($router) {

    $router->get('/dashboard', 'salaries\DashboardController@index');

    
    $router->get('/forum', 'salaries\ForumController@index');
    $router->post('/forum/sujets/{id}/supprimer', 'salaries\ForumController@deleteSujet');
    $router->post('/forum/reponses/{id}/supprimer', 'salaries\ForumController@deleteReponse');

    $router->get('/conseils', 'salaries\ConseilController@index');
    $router->post('/conseils/store', 'salaries\ConseilController@store');
    $router->get('/conseils/{id}/edit', 'salaries\ConseilController@edit');
    $router->post('/conseils/{id}/update', 'salaries\ConseilController@update');
    $router->post('/conseils/{id}/delete', 'salaries\ConseilController@delete');

    
    $router->get('/formations', 'salaries\FormationController@index');
    $router->post('/formations/store', 'salaries\FormationController@store');
    $router->get('/formations/{id}/edit', 'salaries\FormationController@edit');
    $router->post('/formations/{id}/update', 'salaries\FormationController@update');
    $router->post('/formations/{id}/delete', 'salaries\FormationController@delete');
    $router->get('/formations/{id}/etapes', 'salaries\FormationController@etapes');
    $router->post('/formations/{id}/etapes/store', 'salaries\FormationController@etapesStore');
    $router->post('/formations/{id}/etapes/{etapeId}/delete', 'salaries\FormationController@etapesDelete');

    
    $router->get('/evenements', 'salaries\EvenementController@index');
    $router->post('/evenements/store', 'salaries\EvenementController@store');
    $router->get('/evenements/{id}/edit', 'salaries\EvenementController@edit');
    $router->post('/evenements/{id}/update', 'salaries\EvenementController@update');
    $router->post('/evenements/{id}/delete', 'salaries\EvenementController@delete');

   
    $router->get('/ateliers', 'salaries\AtelierController@index');
    $router->post('/ateliers/store', 'salaries\AtelierController@store');
    $router->get('/ateliers/{id}/edit', 'salaries\AtelierController@edit');
    $router->post('/ateliers/{id}/update', 'salaries\AtelierController@update');
    $router->post('/ateliers/{id}/delete', 'salaries\AtelierController@delete');

   
    $router->get('/planning', 'salaries\PlanningController@index');
    $router->post('/planning/evenement/create', 'salaries\PlanningController@storeEvenement');
    $router->post('/planning/formation/create', 'salaries\PlanningController@storeFormation');
    $router->post('/planning/atelier/create', 'salaries\PlanningController@storeAtelier');
    $router->post('/planning/evenement/delete/{id}', 'salaries\PlanningController@deleteEvenement');
    $router->post('/planning/formation/delete/{id}', 'salaries\PlanningController@deleteFormation');
    $router->post('/planning/atelier/delete/{id}', 'salaries\PlanningController@deleteAtelier');

    $router->get('/remboursements', 'salaries\RemboursementController@index');
    $router->post('/remboursements/direct', 'salaries\RemboursementController@direct');
    $router->post('/remboursements/{id}/approuver', 'salaries\RemboursementController@approuver');
    $router->post('/remboursements/{id}/refuser', 'salaries\RemboursementController@refuser');

});