<?php

Route::middleware(['eden_middleware','acces_parametrage'])->name('parametrage.')->prefix('eden/parametrage')->namespace('Eden\Controllers')->group(function() {

    Route::get('', 'Parametrage_controller@index')->name('index');

    Route::post('mode/{changer_mode_parametrage}', 'Utilisateurs_controller@changer_mode_parametrage')->name('changer_mode');

    // Saisie des listes ERP pour la config (lien avec tous les éléments)
    Route::get('elements', 'Parametres_controller@liste_elements')->name('elements');

    // versioning d'Eden
    Route::get('versioning', 'Parametrage_controller@versioning')->name('versioning');

    Route::post('parametre_enregistrer/{nom_parametre}', 'Parametrage_controller@parametre_enregistrer')->name('parametre_enregistrer');

    Route::get('licences', 'Licence_controller@recuperer_licences')->name('recuperer_licences');

    /*
    |##########################################################################
    | Paramétrage de la structure EDEN
    |##########################################################################
    */

    /*
    |--------------------------------------------------------------------------
    | Liste libre
    |--------------------------------------------------------------------------
    |
    | Permet de gérer les listes de l'ERP (listes classiques comme la liste des clients, ou les listes des rapports)
    | Cela inclut les calculs, colonnes, filtres et couleurs des listes
    |
    */
    Route::name('liste_libre.')->namespace('Parametrage')->group(function () {

        Route::prefix('listes_libres')->group(function () {

	        Route::get('', 'Listes_libres_controller@index')->name('liste');

        });

        Route::prefix('liste_libre')->group(function () {

            // détails d'une liste libre
	        Route::get('{id}', 'Listes_libres_controller@liste_libre')->name('index');

            // nouvelle table libre
            Route::post('enregistrer', 'Listes_libres_controller@listes_libres_enregistrer')->name('enregistrer');

            // filtres statiques
	        Route::post('{id}/enregistrer_filtres_appliques', 'Listes_libres_controller@enregistrer_filtres_appliques')->name('enregistrer_filtres_appliques');

            // rapport
            Route::prefix('rapport')->name('rapport.')->group(function () {
                Route::post('enregistrer', 'Listes_libres_controller@rapport_enregistrer')->name('enregistrer');
                Route::post('charger', 'Listes_libres_controller@rapport_charger')->name('charger');
            });

            Route::post('{id}/colonne_enregistrer', 'Listes_libres_controller@colonne_enregistrer')->name('colonne.enregistrer');

            // colonnes
            Route::prefix('colonne')->name('colonne.')->group(function () {
                Route::get('{id}', 'Listes_libres_controller@colonne_recuperer')->name('recuperer');
                Route::get('{id}/supprimer', 'Listes_libres_controller@colonne_supprimer')->name('supprimer');
                Route::post('changement_ordre', 'Listes_libres_controller@changement_ordre_liste_libre')->name('changement_ordre');
                Route::post('changement_etat', 'Listes_libres_controller@changement_etat_colonne')->name('changement_etat');
            });

            Route::post('{id}/filtre_enregistrer', 'Listes_libres_controller@filtre_enregistrer')->name('filtre.enregistrer');

            // filtres
            Route::prefix('filtre')->name('filtre.')->group(function () {

                Route::get('{id}', 'Listes_libres_controller@filtre_recuperer')->name('recuperer');
                Route::get('{id}/supprimer', 'Listes_libres_controller@filtre_supprimer')->name('supprimer');

            });

            Route::post('{id}/calcul_enregistrer', 'Listes_libres_controller@calcul_enregistrer')->name('calcul.enregistrer');

            // calculs
            Route::prefix('calcul')->name('calcul.')->group(function () {

                Route::get('{id}', 'Listes_libres_controller@calcul_recuperer')->name('recuperer');
                Route::get('{id}/supprimer', 'Listes_libres_controller@calcul_supprimer')->name('supprimer');

            });

            // couleurs
            Route::post('{id}/enregistrer_couleurs', 'Listes_libres_controller@enregistrer_couleurs')->name('enregistrer_couleurs');

            // Autres vues
            Route::post('autrevue/supprimer/{id}', 'Listes_libres_controller@supprimer_autrevue')->name('autrevue.supprimer');
            Route::post('{id}/autrevue_enregistrer', 'Listes_libres_controller@enregistrer_autrevue')->name('autrevue.enregistrer');

            // Autres paramètres
            Route::post('{id}/autres_parametres_enregistrer', 'Listes_libres_controller@enregistrer_autres_parametres')->name('autres_parametres.enregistrer');

            // Recherche avancée
            Route::post('{id}/filtres_recherche_avancee_enregistrer', 'Listes_libres_controller@enregistrer_filtres_recherche_avancee')->name('filtres_recherche_avancee.enregistrer');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Tables libres
    |--------------------------------------------------------------------------
    |
    | Permet de gérer les tables libres de l'ERP
    |
    */
    Route::name('table_libre.')->group(function () {

        Route::prefix('tables_libres')->group(function () {

            //Route affichage liste des tables créées
            Route::get('', 'Parametrage\Tables_libres_controller@index')->name('liste');

            // Affichage des tables parametrable par client
            Route::get('principales', 'Parametrage_controller@parametres_global')->name('principales');

        });

        Route::prefix('table_libre')->group(function () {

            Route::get('editer/{type_element}', 'Parametrage\Tables_libres_controller@index_editer_table')->name('editer');

            Route::get('zoom/{type_element}', 'Parametrage_controller@parametres_table_zoom')->name('zoom');

            Route::post('{nom_table_sql}/{nom_sql}/changement_etat', 'Parametrage\Tables_libres_controller@changement_etat')->name('changement_etat');

            Route::post('enregistrer', 'Parametrage\Tables_libres_controller@enregistrer_table_libre')->name('enregistrer');
            Route::get('{id_table}/supprimer', 'Parametrage\Tables_libres_controller@destroy')->name('supprimer');
            Route::get('{id}', 'Parametrage\Tables_libres_controller@recupere_table_libre')->name('recuperer');
            Route::get('ajax/{type_element}', 'Parametrage\Tables_libres_controller@recupere_table_libre_ajax')->name('recupere_ajax');
            Route::post('modification', 'Parametrage\Tables_libres_controller@modification_table_libre')->name('modification');

        });
    });

	/*
    |--------------------------------------------------------------------------
    | Champs libres
    |--------------------------------------------------------------------------
    |
    | Permet de gérer les champs libres de l'ERP
    |
    */
    Route::name('champ_libre.')->namespace('Parametrage')->group(function () {

        Route::prefix('champs_libres')->group(function () {

            Route::get('modification_des_listes_libres', 'Champs_libres_controller@modification_des_listes_libres')->name('modification_des_listes_libres');
            Route::get('{type_element}', 'Champs_libres_controller@index')->name('liste');

        });

        Route::prefix('champ_libre')->group(function () {

            Route::prefix('valeurs_liste_preenregistree')->name('valeurs_liste_preenregistree.')->group(function () {

                Route::get('{type_element}/{nom_sql}/{liste_choix}', 'Champs_libres_controller@liste_preenregistree_valeurs')->name('index');
                Route::post('enregistrer', 'Champs_libres_controller@enregistrer_liste_preenregistree')->name('enregistrer');
            });

            Route::prefix('valeurs_liste')->name('valeurs_liste.')->group(function () {

                Route::post('{id_cl}/enregistrer', 'Champs_libres_controller@enregistrer_valeurs_liste_libre')->name('enregistrer');
                Route::post('enregistrer_liaisons', 'Champs_libres_controller@enregistrer_valeurs_liste_libre_liaisons')->name('enregistrer_liaisons');
                Route::post('supprimer_liaisons', 'Champs_libres_controller@supprimer_valeurs_liste_libre_liaisons')->name('supprimer_liaisons');

                Route::get('{id_cl}', 'Champs_libres_controller@liste_valeurs')->name('index');
            });

            Route::post('creation_table_libre_pivot', 'Champs_libres_controller@creation_table_libre_pivot')->name('creation_table_libre_pivot');
            Route::get('{type_element}/{nom_champ}', 'Champs_libres_controller@recuperer')->name('recuperer');
            Route::post('{type_element}/{nom_champ}/enregistrer', 'Champs_libres_controller@enregistrer')->name('enregistrer');
            Route::get('{type_element}/{nom_champ}/supprimer', 'Champs_libres_controller@supprimer')->name('supprimer');

            Route::post('{type_element}/{id_cl}/changement_etat', 'Champs_libres_controller@changement_etat')->name('changement_etat');
            Route::post('types_elements_dynamiques', 'Champs_libres_controller@recuperer_types_elements')->name('types_elements_dynamiques');

        });
    });

    /*
    |--------------------------------------------------------------------------
    | Formulaires libres
    |--------------------------------------------------------------------------
    */
    Route::name('formulaire.')->namespace('Parametrage')->group(function () {

        Route::get('formulaires', 'Formulaires_controller@index')->name('liste');

        Route::prefix('formulaire')->group(function () {

            Route::get('supprimer/{id_formulaire}', 'Formulaires_controller@listes_formulaires_supprimer')->name('supprimer_liste');
            Route::post('ajouter', 'Formulaires_controller@listes_formulaires_ajouter')->name('ajouter');
            Route::get('ajouter/volee/{type_element}/{type_formulaire?}', 'Formulaires_controller@listes_formulaires_ajouter_volee')->name('ajouter_volee');
            Route::get('ajouter/volee/{nom_formulaire}/parametrable/{type_element}/{type_formulaire?}', 'Formulaires_controller@listes_formulaires_ajouter_volee_parametrable')->name('ajouter_volee_parametrable');
            Route::post('modifier', 'Formulaires_controller@listes_formulaires_modifier')->name('modifier');
            Route::post('supprimer', 'Formulaires_controller@parametrage_formulaire_supprimer')->name('supprimer');
            Route::post('', 'Formulaires_controller@parametrage_formulaire')->name('enregistrer');
            Route::post('enregistre_un_champ', 'Formulaires_controller@parametrage_formulaire_enregistre_un_champ_v2')->name('enregistre_un_champ');
            Route::post('modifier_ordre', 'Formulaires_controller@parametrage_formulaire_modifier_ordre_champ')->name('modifier_ordre');
            Route::post('modifier_taille_libelle', 'Formulaires_controller@parametrage_formulaire_modifier_taille_libelle')->name('modifier_taille_libelle');
            Route::post('modifier_taille_champ', 'Formulaires_controller@parametrage_formulaire_modifier_taille_champ')->name('modifier_taille_champ');
            Route::post('obtenir_options', 'Formulaires_controller@recuperer_options_ajax')->name('obtenir_options');
            Route::post('ajouter_conditions', 'Formulaires_controller@ajouter_conditions_ajax')->name('ajouter_conditions');
            Route::post('enregistrer_valeurs_par_defaut', 'Formulaires_controller@enregistrer_valeurs_par_defaut')->name('enregistrer_valeurs_par_defaut');
            Route::post('generation_iframe', 'Formulaires_controller@generation_iframe')->name('generation_iframe');
            Route::post('ajouter/intranet/{type_element}/{nom_formulaire}', 'Formulaires_controller@creer_formulaire_via_parametrage_intranet')->name('creer_formulaire_intranet');

            Route::get('{nom_formulaire}', 'Formulaires_controller@parametrage_formulaire_index_v2')->name('index');

            Route::name('sous_formulaire.')->prefix('sous_formulaire')->group(function () {

                Route::post('enregistrer/{id_formulaire}', 'Formulaires_controller@enregistrer_sous_formulaire')->name('enregistrer');
                Route::get('recuperer/{nom_sous_formulaire}', 'Formulaires_controller@recupere_sous_formulaire')->name('recuperer');

            });
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Rapport
    |--------------------------------------------------------------------------
    */
    Route::name('rapport.')->group(function () {

        Route::get('rapports', 'Parametrage_controller@rapports')->name('liste');
        Route::post('rapports', 'Parametrage_controller@rapports_enregistrer')->name('liste_enregistrer');

        Route::prefix('rapport')->namespace('Rapports')->group(function () {

            Route::get('creer', 'Rapport_controller@creer_nouveau_rapport')->name('creer');
            Route::get('modifier/{id_rapport}', 'Rapport_controller@creer_nouveau_rapport')->name('modifier');
            Route::get('dupliquer/{id_rapport}', 'Rapport_controller@dupliquer_rapport')->name('dupliquer');
            Route::get('parametrer/{id_rapport}', 'Rapport_controller@parametrer_rapport')->name('parametrer');
            Route::post('enregistrer_parametrage', 'Rapport_controller@enregistrer_parametrage_rapport')->name('enregistrer_parametrage');
            Route::post('modifier', 'Rapport_controller@modifier_rapport')->name('modifier_post');
            Route::post('modifier_ordre', 'Rapport_controller@modifier_ordre_rapport')->name('modifier_ordre');
            Route::get('{id_rapport}/supprimer', 'Rapport_controller@supprimer')->name('supprimer');
            Route::get('{id_rapport}/activer', 'Rapport_controller@activer')->name('activer');

        });
    });

    /*
    |--------------------------------------------------------------------------
    | Fiche
    |--------------------------------------------------------------------------
    */
    Route::name('fiche.')->group(function () {

        Route::get('fiches', 'Parametrage_controller@fiches')->name('liste');

        Route::prefix('fiche/')->group(function () {

            Route::get('{type_element}', 'Parametrage_controller@fiche')->name('index');
            Route::post('{type_element}', 'Parametrage_controller@fiche_enregistrer')->name('enregistrer');
            Route::post('{type_element}/dupliquer/{extranet?}', 'Parametrage_controller@fiche_dupliquer')->name('dupliquer');

        });
    });

    /*
    |##########################################################################
    | Paramétrage des éléments
    |##########################################################################
    */

    /*
    |--------------------------------------------------------------------------
    | Fonctionnalités
    |--------------------------------------------------------------------------
    */
    Route::name('fonctionnalites.')->prefix('fonctionnalites/')->group(function () {

        //gestion des fonctionnalités spécifiques
        Route::name('specifiques.')->prefix('specifiques/')->group(function () {
            Route::get('', 'Parametrage_controller@fonctionnalites_specifiques')->name('index');
            Route::post('', 'Parametrage_controller@fonctionnalites_specifiques_enregistrer')->name('enregistrer');
        });

        Route::get('', 'Parametrage_controller@fonctionnalites')->name('index');
        Route::post('', 'Parametrage_controller@fonctionnalites_enregistrer')->name('enregistrer');
        Route::get('{module}', 'Parametrage_controller@fonctionnalites_module')->name('module');
        Route::post('regenere_composants_lie', 'Parametrage_controller@regenere_composants_lie_fonctionnalite')->name('regenere_composants_lie');
        Route::post('recherche', 'Parametrage_controller@fonctionnalites_recherche')->name('recherche');
        Route::post('deconnexion_api', 'Parametrage_controller@deconnexion_api')->name('deconnexion_api');
        Route::get('export/excel', 'Parametrage_controller@fonctionnalites_exporter_excel')->name('exporter_excel');
        Route::get('recuperer_valeurs', 'Parametrage_controller@recuperer_valeur_fonctionnalites')->name('recuperer_valeurs');

    });

    /*
    |--------------------------------------------------------------------------
    | Utilisateur
    |--------------------------------------------------------------------------
    */
    Route::name('utilisateur.')->group(function () {

        Route::get('utilisateurs', 'Utilisateurs_controller@liste_utilisateurs')->name('liste');

        Route::prefix('utilisateur')->group(function () {

            Route::get('{id}/supprimer', 'Utilisateurs_controller@supprimer')->name('supprimer');
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Menus
    |--------------------------------------------------------------------------
    */
    Route::name('menu.')->prefix('menu/')->namespace('Parametrage')->group(function () {

        Route::post('changement_ordre_menus', 'Menus_controller@changement_ordre_menus')->name('changement_ordre_menus');
        Route::post('desactivation_menu', 'Menus_controller@desactivation_menu')->name('desactivation_menu');
        Route::post('donnees_selection_routes', 'Menus_controller@donnees_selection_routes')->name('donnees_selection_routes');
        Route::get('{extranet?}', 'Menus_controller@menus')->name('index');
    });

    /*
    |--------------------------------------------------------------------------
    | Extranet
    |--------------------------------------------------------------------------
    */
    Route::name('extranet.')->prefix('extranet')->group(function () {

        // configuration des fiches extranet
        Route::get('fiches', 'Parametrage_controller@fiches')->name('fiches');

        Route::get('fiche/{type_element}', 'Parametrage_controller@fiche')->name('fiche');
        Route::post('fiche/{type_element}', 'Parametrage_controller@fiche_enregistrer')->name('fiche_post');

    });

    /*
    |--------------------------------------------------------------------------
    | Intranet
    |--------------------------------------------------------------------------
    */
    Route::name('intranet.')->prefix('intranet')->namespace('Parametrage')->group(function () {

        Route::get('', 'Intranet_controller@parametrage')->name('index');
        Route::post('enregistrer_nouvelle_structure', 'Intranet_controller@enregistrer_nouvelle_structure')->name('enregistrer_nouvelle_structure');
        Route::get('gestion_liste/{type_element}/{id_liste}', 'Intranet_controller@gestion_liste')->name('gestion_liste');
        Route::get('recupere_listes_type_element/{type_element}', 'Intranet_controller@recupere_listes_type_element')->name('recupere_listes_type_element');
        Route::get('recupere_formulaires_type_element/{type_element}', 'Intranet_controller@recupere_formulaires_type_element')->name('recupere_formulaires_type_element');
        Route::get('recupere_liste/{liste_id}', 'Intranet_controller@recupere_liste')->name('recupere_liste');
    });

    /*
    |--------------------------------------------------------------------------
    | Tableau de bord
    |--------------------------------------------------------------------------
    */
    Route::name('tableau_de_bord.')->prefix('tableau_de_bord/')->group(function () {

        Route::post('{id_tableau}/change_ordre', 'Tableau_de_bord_controller@change_ordre')->name('change_ordre');

        Route::name('categorie.')->prefix('categorie/')->group(function () {

            Route::post('enregistrer', 'Tableau_de_bord_controller@enregistrer_categorie')->name('enregistrer');
            Route::post('supprimer', 'Tableau_de_bord_controller@supprimer_categorie')->name('supprimer');

        });

        Route::name('rapport.')->prefix('rapport/')->group(function () {

            Route::post('enregistrer', 'Tableau_de_bord_controller@enregistrer_rapport')->name('enregistrer');
            Route::post('supprimer', 'Tableau_de_bord_controller@supprimer_rapport')->name('supprimer');

        });
    });

    /*
    |--------------------------------------------------------------------------
    | CSS
    |--------------------------------------------------------------------------
    */
    Route::name('css.')->prefix('css/')->group(function () {
        Route::get('{extranet?}', 'Parametrage_controller@css')->name('index');
        Route::post('', 'Parametrage_controller@css_enregistrer')->name('enregistrer');
    });

    /*
    |--------------------------------------------------------------------------
    | Traduction
    |--------------------------------------------------------------------------
    */
    Route::name('traduction.')->prefix('traduction')->group(function () {

        Route::get('', 'Traduction_controller@afficher')->name('index');
        Route::get('categorie/{id_categorie?}', 'Traduction_controller@traductions_categorie')->name('categorie');
        Route::get('mise_a_jour_traductions_valeurs','Traduction_controller@mise_a_jour_traductions_valeurs')->name('mise_a_jour_valeurs');
        Route::post('enregistrer', 'Traduction_controller@enregistrer')->name('enregistrer');
        Route::post('supprimer', 'Traduction_controller@supprimer')->name('supprimer');
        Route::post('recuperer_element', 'Traduction_controller@recuperation_element')->name('recuperer_element');

        Route::get('mode_traduction/{valeur}', 'Traduction_controller@mode_traduction')->name('mode_traduction');
        Route::post('import', 'Traduction_controller@importer')->name('importer');
        Route::post('modifier_en_masse', 'Traduction_controller@modifier_en_masse')->name('modifier_en_masse');
        Route::post('recherche', 'Traduction_controller@recherche_globale')->name('recherche_globale');
    });

    /*
    |--------------------------------------------------------------------------
    | Licence
    |--------------------------------------------------------------------------
    */
    Route::name('licence.')->prefix('licence')->group(function () {

        Route::get('', 'Licence_controller@afficher')->name('index');

    });

    /*
    |--------------------------------------------------------------------------
    | Email
    |--------------------------------------------------------------------------
    */
    Route::name('email.')->prefix('email')->group(function () {
        Route::get('', function(){
            return view('eden::parametrage.email.index');
        })->name('index');

    });

    /*
    |##########################################################################
    | Paramétrage annexes
    |##########################################################################
    */

    /*
    |--------------------------------------------------------------------------
    | Indicateurs
    |--------------------------------------------------------------------------
    */
    Route::name('indicateur.')->prefix('indicateur/')->group(function () {

        Route::get('', 'Parametrage_controller@indicateurs')->name('index');
        Route::post('', 'Parametrage_controller@indicateurs_enregistrer')->name('enregistrer');
        Route::post('recalcule', 'Parametrage_controller@indicateurs_recalcule')->name('recalcule');
    });

    /*
    |--------------------------------------------------------------------------
    | Pdf par défaut
    |--------------------------------------------------------------------------
    */
    Route::name('pdf_par_defaut.')->prefix('pdf_par_defaut/')->group(function () {

        Route::get('', 'Parametrage_controller@gestion_pdf_par_defaut')->name('index');
        Route::post('', 'Parametrage_controller@enregistre_pdf_par_defaut')->name('enregistrer');
    });

    /*
    |--------------------------------------------------------------------------
    | Logs
    |--------------------------------------------------------------------------
    */
    Route::name('logs.')->prefix('logs/')->group(function () {
        Route::get('', 'Parametrage_controller@logs')->name('index');
        Route::post('{action}', 'Parametrage_controller@logs')->name('action');
    });

    /*
    |--------------------------------------------------------------------------
    | Usurpation
    |--------------------------------------------------------------------------
    */
    Route::name('usurpation.')->prefix('usurpation')->group(function () {

        Route::get('retour', 'Authentification_controller@usurpation_retour')->name('retour');
        Route::get('{id_utilisateur}', 'Authentification_controller@usurpation')->name('index');
    });

    /*
    |##########################################################################
    | Paramétrage des intégrations
    |##########################################################################
    */

    /*
    |--------------------------------------------------------------------------
    | Google drive
    |--------------------------------------------------------------------------
    */
    Route::name('gdrive.')->prefix('gdrive')->group(function () {
        Route::get('', 'Parametrage_controller@synchro_gdrive')->name('index');
    });
});

