<?php

/**
 * Extranet
 */
Route::middleware(['eden_extranet'])->name('extranet.')->prefix('/extranet')->namespace('Eden\Controllers\Extranet')->group(function() {

    // Route qui mène à la page de connexion client
    Route::get('login', 'Login_controller@login')->name('login');

    // Route qui mène à la page de connexion client + formulaire effectif
    Route::post('login_post', 'Login_controller@login_post')->name('login_post');

    Route::get('usurpation/{element_id}', 'Login_controller@usurpation_extranet')->name('usurpation');

    // Route correspondant à la méthode chargée de déconnecter les utilisateurs
    Route::get('deconnexion', 'Login_controller@deconnexion')->name('deconnexion');

    // Route de création d'un compte client
    Route::get('inscription/{id}', 'Login_controller@inscription')->name('inscription');

    // Route de validation d'inscription du compte client
    Route::post('validation_inscription', 'Login_controller@validation_inscription')->name('validation_inscription');

    // Route qui mène à la page de récupération de mdp client
    Route::get('mot_de_passe_oublie', 'Login_controller@mot_de_passe_oublie')->name('mot_de_passe_oublie');

    // Route de récupération du mdp
    Route::post('mot_de_passe_oublie_post', 'Login_controller@mot_de_passe_oublie_post')->name('mot_de_passe_oublie_post');

    // Route d'affichage du formulaire de changement de mdp
    Route::get('changer_mot_de_passe/{id}', 'Login_controller@changer_mot_de_passe')->name('changer_mot_de_passe');

    // Route de récupération du formulaire de changement de mot de passe
    Route::post('changer_mot_de_passe_post', 'Login_controller@changer_mot_de_passe_post')->name('changer_mot_de_passe_post');
    
    Route::get('renouvellement_mot_de_passe/{id}', 'Login_controller@renouvellement_mot_de_passe')->name('renouvellement_mot_de_passe');
    Route::post('renouvellement_mot_de_passe', 'Login_controller@renouvellement_mot_de_passe_post')->name('renouvellement_mot_de_passe_post');

    Route::get('code_connexion/{id}', 'Login_controller@code_connexion')->name('code_connexion');
    Route::post('code_connexion', 'Login_controller@retour_code_connexion')->name('retour_code_connexion');
    Route::post('renvoi_mail_code_connexion', 'Login_controller@renvoi_mail_code_connexion')->name('renvoi_mail_code_connexion');


    // Route pour l'url unique des tickets
    Route::get('ticket_client/{id}/{id_user}', 'Ticket_client_controller@lien_unique_vers_ticket')->name('lien_unique_vers_ticket');

    Route::get('connexion_externe', 'Login_controller@connexion_externe')->name('connexion_externe');

    Route::middleware(['eden_utilisateur_connecte','eden_middleware'])->group(function() {

        // Route correspondant à l'accueil lorsque l'utilisateur s'est connecté
        Route::get('accueil', 'Accueil_controller@accueil')->name('accueil');

        Route::get('acces_restreint', 'Accueil_controller@acces_restreint')->name('acces_restreint');

        // Route permettant d'usurper l'identité d'un client en tant que contact
        Route::get('changement_contact/{id}', 'Accueil_controller@changement_contact')->name('changement_contact');

        Route::get('utilisateurs/mes_informations', 'Utilisateur_extranet_controller@mes_informations')->name('utilisateurs.mes_informations');
        /*
        |--------------------------------------------------------------------------
        | Utilisateur_connecté
        |--------------------------------------------------------------------------
        */
        Route::name('utilisateur_connecte.')->prefix('preferences')->group(function () {
            Route::get('', 'Utilisateur_extranet_controller@profil_utilisateur_connecte')->name('index');
            Route::post('enregistrer', 'Utilisateur_extranet_controller@enregistrer_modification_utilisateur_connecte')->name('enregistrer');
            Route::post('double_facteur_application', 'Utilisateur_extranet_controller@changer_double_facteur_application')->name('double_facteur_application');
        });

        Route::post('element/devis_vente/{id_element}/valider', 'Document_controller@devis_vente_valider')->name('devis_vente_valider');
    });
});