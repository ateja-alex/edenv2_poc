<?php

Route::name('maintenance.')->prefix('eden/maintenance')->namespace('Eden\Controllers')->group(function() {

    Route::middleware(['eden_middleware', 'editeur'])->group(function () {

        Route::get('', 'Maintenance_controller@index')->name('index');

        // Vue migration
        Route::get('migrations', 'Maintenance_controller@migrations')->name('migrations');

        Route::post('migrations', 'Maintenance_controller@migrations_post')->name('migrations_traitement');

        /*
        |##########################################################################
        | Débuggage
        |##########################################################################
        */

        // Affichage des logs
        Route::get('log/{type_element}/{id}', 'Element_controller@afficher_logs')->name('affichage_logs');

        Route::get('mode_vuejs/{changer_mode_vuejs}', 'Utilisateurs_controller@changer_mode_vuejs')->name('mode_vuejs');

        Route::get('recuperer_droits_compte_initial/{action}', 'Utilisateurs_controller@recuperer_droits_compte_initial')->name('recuperer_droits_compte_initial');

        Route::get('cache', 'Maintenance_controller@cache')->name('cache');
        Route::get('cache/donnees', 'Maintenance_controller@cache_donnees')->name('cache_donnees');
        Route::get('cache/espace', 'Maintenance_controller@cache_espace')->name('cache_espace');
        Route::post('cache/vider', 'Maintenance_controller@cache_vider')->name('cache_vider');

        Route::get('debug', 'Maintenance_controller@debug')->name('debug');

        // vue test des temps
        Route::get('temps', 'Maintenance_controller@temps')->name('temps');

        /*
        |--------------------------------------------------------------------------
        | Test des urls
        |--------------------------------------------------------------------------
        */
        Route::name('test_url.')->group(function () {

            Route::prefix('test_urls')->group(function () {
                Route::get('{type_test?}', 'Maintenance_controller@test_erreur_500')->name('liste');
            });

            Route::prefix('test_url')->group(function () {

                Route::post('', 'Maintenance_controller@test_url_erreur_500')->name('index');
                Route::get('{methode}', 'Test_controller@test')->name('methode');
                Route::get('element/{type_element}', 'Test_controller@test_element')->name('element');
                Route::get('feature/{feature}', 'Test_controller@test_feature')->name('feature');

            });
        });

        /*
        |##########################################################################
        | Outils
        |##########################################################################
        */

        // affiche la config
        Route::get('affiche_config', 'Maintenance_controller@affiche_config')->name('affiche_config');

        /*
        |--------------------------------------------------------------------------
        | Rappel de version
        |--------------------------------------------------------------------------
        */
        Route::prefix('rappel_version')->name('rappel_version.')->group(function () {

            Route::get('', 'Version_controller@recuperer_rappels_version')->name('index');
            Route::get('confirmation_lecture', 'Version_controller@confirmation_lecture_rappel')->name('confirmation_lecture');
        });

        /*
        |--------------------------------------------------------------------------
        | Génération fichier
        |--------------------------------------------------------------------------
        */
        Route::prefix('generation_fichier')->name('generation_fichier.')->group(function () {

            Route::get('module', 'Cache_controller@generer_module')->name('module');

            Route::prefix('composants')->name('composants.')->group(function () {
                Route::get('', 'Cache_controller@genere_fichiers_composants')->name('index');
                Route::get('liste/{type_a_generer?}/{valeur_a_generer?}', 'Cache_controller@genere_fichiers_composants_liste')->name('liste');
                Route::get('module/{nom_module?}', 'Cache_controller@genere_fichiers_composants_module')->name('module');

            });
            Route::get('menus/{id_profil_menu?}', 'Cache_controller@genere_menus')->name('menus');
            Route::get('css', 'Cache_controller@genere_css')->name('css');
            Route::get('valeurs_champs_listes', 'Cache_controller@genere_valeurs_champs_listes')->name('valeurs_champs_listes');
            Route::get('traductions', 'Cache_controller@genere_traductions')->name('traductions');
        });

        /*
        |--------------------------------------------------------------------------
        | Traduction
        |--------------------------------------------------------------------------
        */
        Route::prefix('traduction')->name('traduction.')->group(function () {

            Route::get('synchronisation', 'Traduction_controller@synchroniser')->name('synchroniser');
            Route::get('synchronisation_environnement', 'Traduction_controller@synchronisation_environnement')->name('synchronisation_environnement');
            Route::post('validation_synchronisation_environnement', 'Traduction_controller@validation_synchronisation_environnement')->name('validation_synchronisation_environnement');
            Route::post('envoie_base_modele', 'Traduction_controller@envoie_base_modele')->name('envoie_base_modele');

        });

        /*
        |--------------------------------------------------------------------------
        | Licence
        |--------------------------------------------------------------------------
        */
        Route::name('licence.')->prefix('licence')->group(function () {

            Route::post('informations', 'Licence_controller@informations')->name('informations');
            Route::post('modules_type_element', 'Licence_controller@modules_type_element')->name('modules_type_element');
            Route::post('ensembles', 'Licence_controller@ensembles')->name('ensembles');
        });

        /*
        |##########################################################################
        | Gestion des données
        |##########################################################################
        */

        // vide les chaines d'affichage
        Route::get('vider_chaine_affichage/{type_element}', 'Parametrage\Tables_libres_controller@vider_chaine_affichage')->name('vider_chaine_affichage');

        // Update les adresses pour avoir latitude / longitude si possible
        Route::get('maj_geolocalisation_adresse', 'Maintenance_controller@mise_a_jour_geolocalisations_adresses')->name('maj_geolocalisation_adresse');

        Route::get('suppresion_filtres_utilisateurs_rapports', 'Maintenance_controller@suppresion_filtres_utilisateurs_rapports')->name('suppresion_filtres_utilisateurs_rapports');;

        Route::get('synchro_suivi_recette', 'Maintenance_controller@synchro_suivi_recette')->name('synchro_suivi_recette');

        Route::get('colonnes_null', 'Maintenance_controller@colonnes_null')->name('colonnes_null');

        Route::get('maj_chaine_tags_ajax/{type_element}', 'Maintenance_controller@maj_chaine_tags_ajax')->name('maj_chaine_tags_ajax');

        Route::get('maj_crons', 'Maintenance_controller@maj_crons')->name('maj_crons');

        Route::get('erreur_struc_table/{type_element}', 'Maintenance_controller@erreur_struc_table')->name('erreur_struc_table');

        Route::prefix('rattrapage_routes')->name('rattrapage_routes.')->group(function () {

            Route::get('', 'Maintenance_controller@rattrapage_routes')->name('index');
            Route::post('', 'Maintenance_controller@rattrapage_routes_post')->name('index_post');

        });

        /*
        |##########################################################################
        | Intégrations
        |##########################################################################
        */

        /*
        |--------------------------------------------------------------------------
        | Outlook
        |--------------------------------------------------------------------------
        */
        Route::prefix('outlook')->name('outlook.')->group(function () {

            //generation manifest plugin outlook
            Route::get('genere_manifest', 'Addon_messagerie_controller@genere_manifest_outlook')->name('genere_manifest');

        });

        /*
        |--------------------------------------------------------------------------
        | Microsoft
        |--------------------------------------------------------------------------
        */
        Route::prefix('microsoft')->name('microsoft.')->group(function () {

            Route::get('id_utilisateur_pour_utilisateurs_existants', 'Maintenance_controller@recupere_id_utilisateur_microsoft_pour_utilisateurs_existants')->name('id_utilisateur_pour_utilisateurs_existants');

        });

        /*
        |--------------------------------------------------------------------------
        | Sharepoint
        |--------------------------------------------------------------------------
        */
        Route::prefix('sharepoint')->name('sharepoint.')->group(function () {

            Route::get('transfert_bibliotheque', 'Maintenance_controller@transfert_bibliotheque_eden_a_sharepoint')->name('transfert_bibliotheque');
            Route::get('creation_dossiers_elements/{type_element?}', 'Maintenance_controller@creation_dossiers_elements_sharepoint')->name('creation_dossiers_elements');

        });
    });
});