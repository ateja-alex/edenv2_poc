<?php

Route::prefix('eden')->namespace('Eden\Controllers')->group(function () {

    /*
    |##########################################################################
    | Login
    |##########################################################################
    */
    //Authentification Client (standard)
    Route::get('login', 'Authentification_controller@login')->name('login');
    Route::get('login_eden', 'Authentification_controller@login_eden')->name('login_eden');
    Route::post('login', 'Authentification_controller@connexion')->name('login_post');
    Route::get('logout', 'Authentification_controller@deconnexion')->name('deconnexion');

    Route::get('utilisateur/initialisation/mot_de_passe/{token}', 'Utilisateurs_controller@initialisation_mot_de_passe')->name('initialisation_mot_de_passe');
    Route::post('utilisateur/initialisation/mot_de_passe/post', 'Utilisateurs_controller@initialisation_mot_de_passe_post')->name('initialisation_mot_de_passe_post');

    Route::get('acces_restreint', 'Authentification_controller@acces_restreint')->name('acces_restreint');

    Route::post('verification_connexion', 'Authentification_controller@verification_connexion')->name('verification_connexion');

    Route::get('mot_de_passe_oublie', 'Authentification_controller@mot_de_passe_oublie')->name('mot_de_passe_oublie');
    Route::post('mot_de_passe_oublie', 'Authentification_controller@mot_de_passe_oublie_envoi_mail');
    Route::get('reinitialisation_mot_de_passe/{id}/{token}', 'Authentification_controller@reinitialisation_mot_de_passe')->name('reinitialisation_mot_de_passe');
    Route::post('reinitialisation_mot_de_passe', 'Authentification_controller@reinitialisation_mot_de_passe_post')->name('reinitialisation_mot_de_passe_post');
    Route::get('renouvellement_mot_de_passe/{id}/{token}', 'Authentification_controller@renouvellement_mot_de_passe')->name('renouvellement_mot_de_passe');
    Route::get('code_connexion/{id}/{token}/{remember_token}', 'Authentification_controller@code_connexion')->name('code_connexion');
    Route::post('code_connexion', 'Authentification_controller@retour_code_connexion')->name('retour_code_connexion');
    Route::post('renvoi_mail_code_connexion', 'Authentification_controller@renvoi_mail_code_connexion')->name('renvoi_mail_code_connexion');

    // Authentification Client (microsoft)
    Route::get('login/microsoft', 'Authentification_controller@login_microsoft');
    Route::get('login/connexion_microsoft', 'Authentification_controller@connexion_microsoft');
    Route::get('login/retour_microsoft', 'Authentification_controller@retour_microsoft');
    Route::get('login/deconnexion_microsoft', 'Authentification_controller@deconnexion_microsoft');

    // Authentification Client (Google)
    Route::get('login/connexion_google', 'Authentification_controller@connexion_google');
    Route::get('login/retour_google', 'Authentification_controller@retour_google');

    Route::middleware(['eden_utilisateur_connecte', 'eden_middleware','eden_extranet'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Campagne de prospection
        |--------------------------------------------------------------------------
        */
        Route::name('campagne_de_prospection.')->group(function () {

            Route::get('fiche/{type_element}/{id}/campagne_de_prospection/{id_campagne}/{flux_liste?}', 'Fiche_controller@afficher_campagne_de_prospection')->name('fiche_element');

            Route::prefix('campagne_de_prospection')->group(function () {

                Route::post('recapitulatif/{id_campagne}', 'Campagne_de_prospection_controller@recapitulatif')->name('recapitulatif');

                // ajouter des contacts à une campagne de prospection
                Route::post('ajouter', 'Campagne_de_prospection_controller@ajouter')->name('ajouter');

                // lancer une campagne de prospection
                Route::get('{id}/{type_element}/{element_id}/lancer', 'Campagne_de_prospection_controller@lancer')->name('lancer');
            });
        });

        /*
        |##########################################################################
        | BASE EDEN
        |##########################################################################
        */

        Route::name('base_eden.')->group(function () {

            // vider le cache
            Route::get('cache/vider', 'Cache_controller@vider')->name('cache_vider');
            
            // Modifier type menu
            Route::post('modifier_type_menu', 'Menu_controller@modifier_type_menu')->name('modifier_type_menu');

            Route::post('recuperer_types_element_via_ids', 'Element_controller@recuperer_types_element_via_ids')->name('recuperer_types_element_via_ids');
            // Liste utilisateurs par équipe
            Route::get('utilisateurs/liste_par_equipe', 'Utilisateurs_controller@utilisateurs_par_equipe')->name('liste_utilisateurs_par_equipe');

            /*
            |--------------------------------------------------------------------------
            | Element
            |--------------------------------------------------------------------------
            */
            Route::name('element.')->group(function () {

                Route::prefix('elements')->group(function () {
                    // Actions en masse
                    Route::post('modifier_en_masse', 'Element_controller@modifier_en_masse')->name('modifier_en_masse');
                    Route::post('supprimer_en_masse', 'Element_controller@supprimer_en_masse')->name('supprimer_en_masse');
                    Route::post('imprimer_en_masse', 'Element_controller@imprimer_en_masse')->name('imprimer_en_masse');
                    Route::post('taches_en_masse', 'Element_controller@taches_en_masse')->name('taches_en_masse');

                    Route::post('affichage', 'Element_controller@affichage_elements')->name('affichage');

                    Route::post('{type_element}', 'Element_controller@recuperer_elements')->name('recuperer_liste');

                });

                Route::prefix('element')->group(function () {

                    Route::post('{type_element}/rechercher-avec-requete', 'Element_controller@rechercher_avec_requete')->name('rechercher_avec_requete');

                    Route::get('{type_element}/{id}/afficher_pdf/{nom_du_pdf?}', 'Element_controller@afficher_pdf')->name('afficher_pdf');

                    Route::get('{type_element}/{id}/generer_facture_maintenance_intervention', 'Element_controller@generer_facture_maintenance_intervention')->name('generer_facture_maintenance_intervention');

                    Route::post('kanban/modification', 'Element_controller@changer_etat_kanban')->name('changer_kanban');
                    Route::post('kanban/ordre/{type_element}/{id_element}/{position}', 'Element_controller@changer_ordre_kanban')->name('changer_ordre_kanban');
                    Route::post('kanban/ordre_dans_colonne_kanban', 'Element_controller@ordre_dans_colonne_kanban')->name('ordre_dans_colonne_kanban');

                    Route::post('{type_element}/{id}/valider_action', 'Element_controller@valider_action')->name('valider_action');
                    Route::post('{type_element}/{id}/refuser_action', 'Element_controller@refuser_action')->name('refuser_action');

                    Route::get('{type_element}/{id}/demander_approbation_manuelle', 'Element_controller@demander_approbation_manuelle')->name('demander_approbation_manuelle');

                    Route::post('valider_en_masse', 'Element_controller@valider_en_masse')->name('valider_en_masse');

                    Route::post('{type_element}/recuperer_tous_les_elements_ajax', 'Element_controller@recuperer_tous_les_elements_ajax')->name('recuperer_tous_les_elements');
                    Route::post('{type_element}/{id}/valeurs_champs_relies', 'Element_controller@valeurs_champs_relies')->name('valeurs_champs_relies');

                    Route::post('{type_element}/{id}/enregistrer_commentaires_fiche', 'Element_controller@enregistrer_commentaires_fiche')->name('enregistrer_commentaires_fiche');

                    Route::get('{type_element}/{id}/traductions/{langue}', 'Element_controller@recupere_traductions')->name('recuperer_traductions');
                    Route::post('{type_element}/{id}/traductions/{langue}', 'Element_controller@enregistre_traductions')->name('enregistre_traductions');

                    Route::post('recherche/{type_element}/{recherche}', 'Element_controller@rechercher')->name('rechercher')->where('recherche', '(.*)');
                    Route::post('{type_element}/{id}/enregistrer', 'Element_controller@enregistrer')->name('enregistrer');
                    Route::post('{type_element}/{id}/test_enregistrement', 'Element_controller@test_enregistrement')->name('test_enregistrement');
                    Route::post('{type_element}/creer', 'Element_controller@creer')->name('creer');
                    Route::post('{type_element}/test_creation', 'Element_controller@test_creation')->name('test_creation');
                    Route::get('{type_element}/{id}/supprimer', 'Element_controller@supprimer')->name('supprimer');
                    Route::get('{type_element}/modele_par_defaut', 'Element_controller@modele_par_defaut')->name('modele_par_defaut');
                    Route::get('{type_element}/{id_element}', 'Element_controller@recuperer')->name('recuperer');
                    Route::post('convertir', 'Element_controller@convertir')->name('convertir');

                });
            });

            /*
            |--------------------------------------------------------------------------
            | Liste
            |--------------------------------------------------------------------------
            */
            Route::name('liste.')->group(function () {

                Route::post('listes/initialisation', 'Liste_controller@initialisation_listes')->name('initialisation_multiple');

                Route::prefix('liste')->group(function () {

                    Route::post('enregistrer_filtre', 'Liste_controller@enregistrer_filtres')->name('enregistrer_filtre');
                    Route::post('supprimer_filtre', 'Liste_controller@supprimer_filtre')->name('supprimer_filtre');
                    // Détails d'une ligne d'une liste
                    Route::post('detail/ligne', 'Element_controller@recuperer_details_ligne_pour_liste')->name('detail_ligne');


                    Route::get('rapport/{rapport_id}', 'Rapports\Rapport_controller@rapport')->name('rapport');

                    // pages génériques
                    Route::get('{type_element}', 'Liste_controller@afficher')->name('index');

                    Route::get('{type_element}/filtre/{id_filtre}', 'Liste_controller@afficher')->name('avec_filtre');

                    Route::get('{type_element}/kanban/{colonne}', 'Liste_controller@afficher_kanban')->name('kanban');
                    Route::get('{type_element}/kanban_avec_somme/{colonne}/{colonne_somme}/{unite?}', 'Liste_controller@afficher_kanban_avec_somme')->name('kanban_avec_somme');
                    Route::get('{type_element}/kanban_avec_nombre/{colonne}/{unite?}', 'Liste_controller@afficher_kanban_avec_nombre')->name('kanban_avec_nombre');

                    Route::post('{type_element}/recuperer_filtres', 'Liste_controller@recuperer_filtres')->name('recuperer_filtres');

                    Route::get('{id_liste}/recupere_ids', 'Liste_controller@recupere_ids')->name('recupere_ids');

                    Route::post('{id_liste}/calculs_elements_selectionnes', 'Liste_controller@calculs_elements_selectionnes')->name('calculs_elements_selectionnes');
                    Route::post('{id_liste}', 'Liste_controller@actualiser')->name('actualiser');

                    Route::post('{id_liste}/exporter/{type_export?}', 'Liste_controller@exporter')->name('export');
                    Route::post('{id_liste}/exporter_modele/{id_rapport?}', 'Liste_controller@exporter_modele')->name('export_modele');

                });
            });

            /*
            |--------------------------------------------------------------------------
            | Champ
            |--------------------------------------------------------------------------
            */
            Route::name('champ.')->group(function () {

                Route::prefix('champs')->group(function () {

                    Route::get('valeurs/{type_element}/{nom_sql?}', 'Parametrage\Champs_libres_controller@recuperer_champs_libres_type_element')->name('recuperer');
                    Route::post('valeurs_multiples', 'Parametrage\Champs_libres_controller@valeurs_multiples')->name('valeurs_multiples');
                    Route::get('tables_jointes/{type_element}', 'Parametrage\Champs_libres_controller@recuperation_tables_jointes')->name('tables_jointes');
                    Route::post('modification_en_masse/{type_element}', 'Parametrage\Champs_libres_controller@modification_en_masse')->name('modification_en_masse');
                    Route::post('variables_champ_date', 'Parametrage\Champs_libres_controller@variables_champ_date')->name('variables_champ_date');
                    Route::post('filtrage/{type_element}', 'Parametrage\Champs_libres_controller@filtrage')->name('filtrage');
                    Route::post('conversion', 'Parametrage\Champs_libres_controller@conversion')->name('conversion');

                });

                Route::prefix('champ')->group(function () {

                    Route::get('{type_element}/{nom_sql}/valeurs', 'Parametrage\Champs_libres_controller@valeurs_possibles_pour_champ')->name('valeurs');
                    Route::get('tableau/{type_element}/{element_id}/{colonne}', 'Parametrage\Champs_libres_controller@recuperer_tableau')->name('tableau');
                    Route::get('methode/{type_element}/{nom_sql}/{methode}', 'Parametrage\Champs_libres_controller@methode_champ')->name('methode');

                });
            });

            /*
            |--------------------------------------------------------------------------
            | Rapports
            |--------------------------------------------------------------------------
            */
            Route::name('rapport.')->group(function () {

                Route::get('rapports', 'Rapports\Rapport_controller@liste_des_rapports')->name('liste');

                Route::prefix('rapport')->group(function () {

                    Route::get('{id_rapport}', 'Rapports\Rapport_controller@rapport')->name('index');
                    Route::post('{id_rapport}', 'Rapports\Rapport_controller@info_rapport')->name('index_post');
                    Route::post('{id_rapport}/abonnement/{frequence}', 'Rapports\Rapport_controller@abonnement')->name('abonnement');
                    Route::post('ajax/{rapport}', 'Rapports\Rapport_controller@rapport_ajax')->name('ajax');
                    Route::post('excel/{id_rapport}', 'Rapports\Rapport_controller@export_excel')->name('export_excel');
                    Route::get('pdf/{id_rapport}/{orientation?}/{format_papier?}', 'Rapports\Rapport_controller@export_pdf')->name('export_pdf');
                    Route::get('{id_indicateur}/liste_indicateur', 'Liste_controller@afficher_avec_indicateur')->name('liste_avec_indicateur');
                    Route::get('favoris/{id_rapport}', 'Rapports\Rapport_controller@gestion_favoris')->name('gestion_favoris');

                });
            });

            /*
            |--------------------------------------------------------------------------
            | Formulaire
            |--------------------------------------------------------------------------
            */
            Route::name('formulaire.')->prefix('formulaire')->group(function () {
                Route::get('{type_element}', 'Element_controller@afficher_formulaire_creation_rapide')->name('index');
                Route::post('affichage', 'Formulaire_controller@formulaire')->name('affichage');
                Route::post('sous_formulaire/affichage', 'Formulaire_controller@sous_formulaire')->name('sous_formulaire.affichage');
                Route::get('autocomplete/recuperation_donnees/{type_element}/{type_recherche?}', 'Autocomplete_controller@recuperation_donnees')->name('autocomplete.recuperation_donnees');
            });

            /*
            |--------------------------------------------------------------------------
            | Fiche
            |--------------------------------------------------------------------------
            */
            Route::name('fiche.')->prefix('fiche')->group(function () {

                Route::get('liste_elements_enfants/{type_element_parent}/{type_element_enfant}/{id_element_parent}', 'Fiche_controller@liste_elements_enfants')->name('liste_elements_enfants');

                Route::post('ajouter_supprimer_abonnement_fiche', 'Fiche_controller@ajouter_supprimer_abonnement_fiche')->name('ajouter_supprimer_abonnement');

                Route::post('modifier_piece_jointe', 'Fiche_controller@modifier_piece_jointe')->name('modifier_piece_jointe');
                Route::post('modifier_piece_jointe_lier_au_document', 'Fiche_controller@changement_statut_piece_jointe_lier_au_document')->name('modifier_piece_jointe_lier_au_document');
                Route::post('recuperer_bibliotheque_parametrer', 'Fiche_controller@recuperer_bibliotheque_parametrer')->name('recuperer_bibliotheque_parametrer');

                Route::post('{type_element}/gestion_options_suppression', 'Fiches\Client_controller@gestion_transfert_suppression')->name('gestion_options_suppression');

                Route::get('{type_element}/pas_les_droits', 'Fiche_controller@pas_droits_affichage_fiche')->name('pas_droits');

                Route::post('{type_element}/{id}/element_historique_creer/{id_echange?}', 'Fiches\Client_controller@element_historique_creer')->name('historique_creer');
                Route::post('{type_element}/{id}/element_historique_supprimer/{id_echange}', 'Fiches\Client_controller@element_historique_supprimer')->name('historique_supprimer');

                Route::get('{type_element}/{id}/rapport/{id_rapport}', 'Fiche_controller@affiche_rapport')->name('affiche_rapport');

                Route::get('{type_element}/{id}/supprimer_image/{id_image}', 'Fiche_controller@supprimer_image')->name('supprimer_image');
                Route::get('{type_element}/{id}/supprimer_image_ajax/{id_image}', 'Fiche_controller@supprimer_image_ajax')->name('supprimer_image_ajax');
                Route::get('{type_element}/{id}/supprimer_piece_jointe/{id_piece_jointe}', 'Fiche_controller@supprimer_piece_jointe')->name('supprimer_piece_jointe');

                Route::post('{type_element}/{id}/mise_a_jour_workflow', 'Fiche_controller@mise_a_jour_workflow')->name('mise_a_jour_workflow');

                Route::get('{type_element}/{id}/pdf_modele/{id_modele_de_document}/{enregistrement_pdf?}', 'Fiche_controller@generer_fiche_pdf_modele')->name('generer_pdf');
                Route::get('{type_element}/{id}/generer_pdf_depuis_modele/{id_modele_de_document}', 'Fiche_controller@generer_pdf_depuis_modele')->name('generer_pdf_depuis_modele');

                Route::post('{type_element}/{id}/post/{methode}', 'Fiche_controller@execute_post')->name('index_post');
                Route::get('{type_element}/{id}/{methode?}/{argument?}', 'Fiche_controller@execute')->name('index');

            });

            /*
            |--------------------------------------------------------------------------
            | Accueil
            |--------------------------------------------------------------------------
            */
            Route::name('accueil.')->prefix('accueil')->group(function () {
                Route::get('{page?}', 'Accueil_controller@accueil')->name('index');
                Route::get('{page?}/{fonction?}', 'Accueil_controller@action')->name('fonction');
            });

            /*
            |--------------------------------------------------------------------------
            | Tableau de bord
            |--------------------------------------------------------------------------
            */
            Route::name('tableau_de_bord.')->prefix('tableau_de_bord')->group(function () {

                Route::get('{id_tableau}', 'Tableau_de_bord_controller@affiche')->name('index');
                Route::post('{id_tableau}/enregistrer_parametres', 'Tableau_de_bord_controller@enregistrer_parametres')->name('enregistrer_parametres');

            });

            /*
            |--------------------------------------------------------------------------
            | Utilisateur_connecté
            |--------------------------------------------------------------------------
            */
            Route::name('utilisateur_connecte.')->prefix('preferences')->group(function () {

                Route::get('', 'Utilisateurs_controller@profil_utilisateur_connecte')->name('index');
                Route::post('enregistrer', 'Utilisateurs_controller@enregistrer_modification_utilisateur_connecte')->name('enregistrer');
                Route::post('double_facteur_application', 'Utilisateurs_controller@changer_double_facteur_application')->name('double_facteur_application');
            });

            /*
            |--------------------------------------------------------------------------
            | Utilisateur extranet
            |--------------------------------------------------------------------------
            */
            Route::name('utilisateur_extranet.')->prefix('utilisateur_extranet')->group(function () {
                Route::post('ajout_contact_en_masse', 'Extranet\Utilisateur_extranet_controller@ajout_contact_en_masse')->name('ajout_contact_en_masse');
                Route::post('envoi_mail_inscription', 'Extranet\Utilisateur_extranet_controller@envoi_mail_inscription')->name('envoi_mail_inscription');
            });

            /*
            |--------------------------------------------------------------------------
            | Aide contextuelle
            |--------------------------------------------------------------------------
            */
            Route::name('aide_contextuelle.')->prefix('aide_contextuelle')->group(function () {

                Route::get('', 'Parametrage_controller@gestion_aide_contextuelle')->name('index');

                Route::get('retour', 'Parametrage_controller@retourne_aide_contextuelle_utilisateur')->name('retour');

            });

            /*
            |--------------------------------------------------------------------------
            | Corbeille
            |--------------------------------------------------------------------------
            */
            Route::name('corbeille.')->prefix('corbeille')->group(function () {

                Route::get('{type_element}', 'Liste_controller@afficher_corbeille')->name('liste');
                Route::get('{type_element}/{id}/retablir', 'Element_controller@retablir')->name('retablir');
            });

            /*
            |--------------------------------------------------------------------------
            | Paramétres erp
            |--------------------------------------------------------------------------
            */
            Route::name('parametres_erp.')->prefix('parametres_erp')->group(function () {

                Route::post('enregistrer', 'Parametres_erp_controller@enregistrer')->name('enregistrer');
                Route::get('recuperer_parametre/{nom}/{type?}/{variable?}', 'Parametres_erp_controller@recuperer_parametre')->name('recuperer');

            });

            /*
            |--------------------------------------------------------------------------
            | Notifications
            |--------------------------------------------------------------------------
            */
            Route::name('notifications.')->prefix('notifications')->group(function () {

                Route::post('recuperer', 'Notifications_controller@recuperer')->name('recuperer');
                Route::post('enregistrer_comme_vues', 'Notifications_controller@enregistrer_comme_vues')->name('enregistrer_comme_vues');
                Route::post('activer_suivi_element', 'Notifications_controller@activer_suivi_element')->name('activer_suivi_element');
                Route::post('recuperer_suivi_element', 'Notifications_controller@recuperer_suivi_element')->name('recuperer_suivi_element');

            });

            Route::name('recherche_avancee.')->prefix('recherche_avancee')->group(function (){
                Route::post('donnees_initialisation', 'Recherche_avancee_controller@donnees_initialisation')->name('donnees_initialisation');
                Route::post('listes', 'Recherche_avancee_controller@listes')->name('listes');
                Route::post('champs_libres', 'Recherche_avancee_controller@champs_libres')->name('champs_libres');
                Route::post('enregistrer', 'Recherche_avancee_controller@enregistrer')->name('enregistrer');
                Route::get('{type}/recherche_type', 'Recherche_avancee_controller@recherche_type')->name('recherche_type');
                Route::get('{id}/supprimer', 'Recherche_avancee_controller@supprimer')->name('supprimer');
                Route::get('{id}', 'Recherche_avancee_controller@recuperer')->name('recuperer');
            });
            
        });

        /*
        |##########################################################################
        | EDEN CRM
        |##########################################################################
        */

        /*
        |--------------------------------------------------------------------------
        | Calendrier
        |--------------------------------------------------------------------------
        */
        Route::name('calendrier.')->group(function () {

            Route::get('mon_calendrier', 'Calendrier_controller@mon_calendrier')->name('mon_calendrier');

            Route::prefix('calendrier')->group(function() {
                Route::get('', 'Calendrier_controller@afficher')->name('afficher');
                Route::post('recuperation_donnees', 'Calendrier_controller@recuperation_donnees')->name('recuperation_donnees');
                Route::get('{id_tache}/supprimer_recurrence', 'Calendrier_controller@supprimer_recurrence')->name('supprimer_recurrence');
                Route::get('{id_tache}/recuperer_recurrence', 'Calendrier_controller@recuperer_recurrence')->name('recuperer_recurrence');
                Route::get('/imprimer/{semaine_voulue}', 'Calendrier_controller@imprimer')->name('imprimer');
                Route::get('/envoyer_par_mail/{semaine_voulue}', 'Calendrier_controller@envoyer_par_mail')->name('envoyer_par_mail');
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Planning
        |--------------------------------------------------------------------------
        */
        Route::name('planning.')->prefix('planning')->group(function () {
            Route::get('/', 'Planning_controller@index')->name('index');
            Route::post('/initialisation', 'Planning_controller@initialisation')->name('initialisation');
            Route::post('/actualisation', 'Planning_controller@actualisation')->name('actualisation');
            Route::get('/imprimer/{semaine_voulue}', 'Planning_controller@imprimer')->name('imprimer');
            Route::get('/envoyer_par_mail/{semaine_voulue}', 'Planning_controller@envoyer_par_mail')->name('envoyer_par_mail');
        });

        Route::name('tache.')->prefix('tache')->group(function () {
            Route::post('{element_id}/autres_affectations', 'Tache_controller@autres_affectations')->name('autres_affectations');
            Route::post('{element_id}/recuperer_details', 'Tache_controller@recuperer_details')->name('recuperer_details');
            Route::post('rechercher_participants_possibles', 'Tache_controller@rechercher_participants_possibles')->name('rechercher_participants_possibles');
        });

        /*
        |--------------------------------------------------------------------------
        | Timeline
        |--------------------------------------------------------------------------
        */
        Route::name('timeline.')->prefix('timeline')->group(function () {
            Route::post('/chargement_donnees', 'Timeline_controller@chargement_donnees')->name('chargement_donnees');
        });

        /*
        |--------------------------------------------------------------------------
        | Saisie des temps
        |--------------------------------------------------------------------------
        */
        Route::name('saisie_des_temps.')->prefix('saisie_des_temps')->group(function () {

            Route::name('regie.')->prefix('regie')->group(function () {

                Route::get('', 'Saisie_des_temps_regie_controller@index')->name('regie');
                Route::get('initialisation', 'Saisie_des_temps_regie_controller@initialisation')->name('initialisation');
                Route::get('generer_facture/{type_element}/{element_id}', 'Saisie_des_temps_regie_controller@generer_facture')->name('generer_facture');
                Route::get('recupere_element/{type_element}/{element_id}', 'Saisie_des_temps_regie_controller@recupere_element')->name('recupere_element');
            });

            Route::name('interne.')->prefix('interne')->group(function () {

                Route::get('', 'Saisie_des_temps_interne_controller@index')->name('index');
                Route::get('informations/{date}/{initialisation}', 'Saisie_des_temps_interne_controller@informations')->name('informations');

            });

            Route::get('', 'Saisie_des_temps_controller@saisie_des_temps')->name('index');

            Route::post('charger_dates', 'Saisie_des_temps_controller@charger_dates')->name('charger_dates');
            Route::post('chargement_donnees/{type_saisie?}', 'Saisie_des_temps_controller@chargement_donnees')->name('chargement_donnees');
            Route::post('chargement_recapitulatif', 'Saisie_des_temps_controller@chargement_recapitulatif')->name('chargement_recapitulatif');
            Route::post('desactiver_element', 'Saisie_des_temps_controller@desactiver_element')->name('desactiver_element');
            Route::post('changer_statut_saisie/{type}', 'Saisie_des_temps_controller@changer_statut_saisie')->name('changer_statut_saisie');
        });

        /*
        |--------------------------------------------------------------------------
        | Suivi des temps travaillés
        |--------------------------------------------------------------------------
        */
        Route::name('suivi_jours_travailles.')->prefix('suivi_jours_travailles')->group(function () {

            Route::get('', 'Suivi_jours_travailles_controller@suivi_jours_travailles')->name('index');
            Route::post('initialisation', 'Suivi_jours_travailles_controller@initialisation')->name('initialisation');
            Route::post('actualisation', 'Suivi_jours_travailles_controller@actualisation')->name('actualisation');
            Route::post('changer_statut_saisie', 'Suivi_jours_travailles_controller@changer_statut_saisie')->name('changer_statut_saisie');
        });

        /*
       |--------------------------------------------------------------------------
       | Envoie des mails
       |--------------------------------------------------------------------------
       */
        Route::name('email.')->prefix('email')->group(function () {

            Route::post('initialisation', 'Email_controller@initialisation')->name('initialisation');
            Route::post('envoyer', 'Email_controller@envoyer')->name('envoi');
            Route::post('chargement/{type}', 'Email_controller@chargement_valeurs')->name('chargement_valeurs');
            Route::post('gestion_publipostage', 'Email_controller@gestion_publipostage')->name('gestion_publipostage');

            // validation d'un compte email
            Route::get('compte/{id}/valider/{token}', 'Compte_email_controller@valider')->name('valider_compte');

            // affiche le contenu d'un email
            Route::get('{id}', 'Mail_controller@affiche_email')->name('affichage');
        });

        /*
       |--------------------------------------------------------------------------
       | Gestion emails recus
       |--------------------------------------------------------------------------
       */
        Route::name('email_recus.')->prefix('email_recus')->group(function () {

            Route::post('{id_email_recu}/charger_pieces_jointes', 'Email_recus_controller@charger_pieces_jointes')->name('charger_pieces_jointes');
        });

        /*
        |--------------------------------------------------------------------------
        | Gestion de la bibliothèque
        |--------------------------------------------------------------------------
        */
        Route::name('bibliotheque.')->prefix('bibliotheque')->group(function () {

            // upload d'un fichier
            Route::post('upload', 'Upload_controller@upload')->name('upload_fichier');

            Route::get('afficher', 'Bibliotheque_controller@afficher')->name('index');
            Route::post('ajouter_fichier', 'Bibliotheque_controller@ajouter_fichier')->name('ajouter_fichier');
            Route::post('ajouter_fichier_element', 'Bibliotheque_controller@ajouter_fichier_element')->name('ajouter_fichier_element');

            Route::get('afficher/{fichier}', 'Bibliotheque_controller@afficher_fichier')->name('afficher_fichier');
            Route::get('afficher/{type_element}/{nom_sql}/{id_element}', 'Bibliotheque_controller@afficher_fichier_via_champ_libre')->name('afficher_via_champ_libre');

            Route::get('fichier/{id_fichier}/supprimer', 'Bibliotheque_controller@supprimer_fichier')->name('supprimer_fichier');
            Route::get('fichier/{id_fichier}/telecharger', 'Bibliotheque_controller@telecharger_fichier')->name('telecharger_fichier');
            Route::get('fichier/telecharger_fichier_element', 'Bibliotheque_controller@telecharger_fichier_element')->name('telecharger_fichier_element');

            Route::post('ajouter_dossier', 'Bibliotheque_controller@ajouter_dossier')->name('ajouter_dossier');
            Route::post('modifier_dossier', 'Bibliotheque_controller@modifier_dossier')->name('modifier_dossier');
            Route::get('dossier/{id_dossier}/supprimer', 'Bibliotheque_controller@supprimer_dossier')->name('supprimer_dossier');
            Route::post('recuperer_bibliotheque_parametrer', 'Bibliotheque_controller@recuperer_bibliotheque_parametrer')->name('recuperer_bibliotheque_parametrer');
            Route::post('deplacer_element_dans_un_dossier', 'Bibliotheque_controller@deplacer_element_dans_un_dossier')->name('deplacer_element_dans_un_dossier');

        });

        /*
        |--------------------------------------------------------------------------
        | Intranet
        |--------------------------------------------------------------------------
        */
        Route::name('intranet.')->prefix('intranet')->group(function () {

            // Page générique intranet
            Route::get('', 'Intranet_controller@afficher')->name('index');

            Route::get('employes_annuaires', 'Intranet_controller@employes_annuaires')->name('employes_annuaires');

            //Affichage d'une erreur s'il la demande est mal effectué
            Route::get('erreur_demande', 'Intranet_controller@erreur_demande')->name('erreur_demande');
        });

        /*
        |--------------------------------------------------------------------------
        | Note de frais
        |--------------------------------------------------------------------------
        */
        Route::name('note_de_frais.')->prefix('note_de_frais')->group(function () {

            // validation des notes de frais
            Route::post('changement_statut', 'Note_de_frais_controller@changement_statut')->name('changement_statut');
            Route::get('validation_note/{id_demande}/{reponse}', 'Note_de_frais_controller@validation_note')->name('validation_note');

            //exportation en pdf des notes de frais
            Route::post('export_pdf', 'Note_de_frais_controller@export_pdf')->name('export_pdf');
            Route::get('{id}/valeurs_tva', 'Note_de_frais_controller@recuperer_valeur_tva')->name('valeurs_tva');
            Route::get('informations', 'Note_de_frais_controller@informations')->name('informations');
        });

        /*
        |--------------------------------------------------------------------------
        | Demandes de congés
        |--------------------------------------------------------------------------
        */
        Route::name('employe_demande_conge.')->prefix('employe_demande_conge')->group(function () {

            Route::post('changement_statut_conge', 'Employe_demande_conge_controller@changement_statut_conge')->name('changement_statut_conge');
            Route::get('validation_conge/{id_demande}/{reponse}', 'Employe_demande_conge_controller@validation_conge')->name('validation_conge');
        });

        /*
        |--------------------------------------------------------------------------
        | Import sur mesure
        |--------------------------------------------------------------------------
        */
        Route::name('import_sur_mesure.')->prefix('import_sur_mesure')->group(function () {

            // import dans la bdd depuis fichier csv
            Route::post('', 'Import_sur_mesure_controller@traitement_donnee')->name('traitement_donnee');
            Route::post('import', 'Import_sur_mesure_controller@traitement_mise_en_bdd')->name('traitement_bdd');

            Route::post('correspondance_valeurs_liste', 'Import_sur_mesure_controller@correspondance_valeurs_liste')->name('correspondance_valeurs_liste');
            Route::post('verifications_cles', 'Import_sur_mesure_controller@verifications_cles')->name('verifications_cles');
            Route::post('enregistrer_champs', 'Import_sur_mesure_controller@enregistrer_champs_import')->name('enregistrer_champs');
            Route::get('calcule_delai_reglement_facture_vente', 'Import_sur_mesure_controller@calcule_delai_reglement_facture_vente')->name('calcule_delai_reglement_facture_vente');

            Route::get('{id?}', 'Import_sur_mesure_controller@afficher')->name('index');
        });

        /*
        |--------------------------------------------------------------------------
        | Ticket client
        |--------------------------------------------------------------------------
        */
        Route::name('ticket_client.')->prefix('ticket_client')->group(function () {

            Route::get('echanges/{ticket_client_id}', 'Fiches\Ticket_client_controller@echanges_ticket')->name('echanges');

        });

        /*
        |##########################################################################
        | EDEN ERP
        |##########################################################################
        */

        /*
        |--------------------------------------------------------------------------
        | Articles
        |--------------------------------------------------------------------------
        */
        Route::name('article.')->group(function () {

            Route::get('articles', 'Article_controller@index_articles')->name('liste');


            Route::prefix('article')->group(function () {

                Route::post('creer_conditionnement_en_masse', 'Article_controller@creer_conditionnement_en_masse')->name('creer_conditionnement_en_masse');

                Route::post('conditionnements', 'Article_controller@recupere_conditionnement')->name('conditionnements');

                Route::post('copier_categories_comptables_article', 'Article_controller@copier_categories_comptables_article')->name('copier_categories_comptables_article');

                Route::get('{article_id}/eco_contribution', 'Article_controller@eco_contribution')->name('eco_contribution');

            });
        });

        /*
        |--------------------------------------------------------------------------
        | Gestion des documents commerciaux
        |--------------------------------------------------------------------------
        */
        Route::name('document.')->prefix('document')->group(function () {

            // Gestion des documents de gestion commerciale
            Route::post('recupere_article/{id}/{type_element}', 'Document_controller@recupere_article_pour_document')->name('recupere_article');

            Route::post('conditions_commerciales', 'Document_controller@conditions_commerciales')->name('conditions_commerciales');

            Route::post('recherche_article/{type_element}/{recherche}', 'Document_controller@rechercher_article')->name('element_rechercher')->where('recherche', '(.*)');

            Route::get('remplace_id_article/{article_id}/{ligne_id}/{type_element}', 'Document_controller@remplace_id_article')->name('remplace_id_article');

            Route::post('articles_via_fournisseur', 'Document_controller@articles_via_fournisseur')->name('articles_via_fournisseur');

            Route::post('mise_a_jour_tarif/{tarif_uniquement?}', 'Document_controller@mise_a_jour_tarif')->name('mise_a_jour_tarif');

            Route::post('calcule_date_reglement', 'Document_controller@calcule_date_reglement')->name('calcule_date_reglement');

            Route::get('recupere_catalogue_articles', 'Document_controller@recupere_catalogue_articles')->name('recupere_catalogue_articles');
            Route::post('recupere_modele_de_calculateur', 'Document_controller@recupere_modele_de_calculateur')->name('recupere_modele_de_calculateur');

            Route::get('infos_projet/{id}', 'Document_controller@infos_projet')->name('infos_projet');

            Route::post('verification_articles_supprimes', 'Document_controller@verification_articles_supprimes')->name('verification_articles_supprimes');

            Route::post('calculer_total', 'Document_controller@calculer_total')->name('calculer_total');

            // Totaux d'un document déjà enregistré (dont la ventilation de TVA), à partir de son seul id
            Route::get('{type_element}/{id_element}/totaux', 'Document_controller@totaux')->name('totaux');

            Route::get('versionning/{id}/afficher_pdf_versionning', 'Document_controller@afficher_pdf_versionning')->name('afficher_pdf_versionning');

            // actions en masse
            Route::post('valider_documents_en_masse', 'Document_controller@valider_en_masse')->name('valider_documents_en_masse');
            Route::post('envoyer_facturation_electronique_en_masse', 'Document_controller@envoyer_facturation_electronique_en_masse')->name('envoyer_facturation_electronique_en_masse');
            Route::post('accepter_documents_en_masse', 'Document_controller@accepter_en_masse')->name('accepter_documents_en_masse');
            Route::post('refuser_documents_en_masse', 'Document_controller@refuser_en_masse')->name('refuser_documents_en_masse');
            Route::post('export_sepa_en_masse', 'Document_controller@export_sepa_en_masse')->name('export_sepa_en_masse');
            Route::post('export_relance_pdf_en_masse', 'Document_controller@export_relance_pdf_en_masse')->name('export_relance_pdf_en_masse');
            Route::post('reception_lignes_en_masse', 'Document_controller@reception_lignes_en_masse')->name('reception_lignes_en_masse');
            Route::post('imprimer_en_masse_documents_commerce', 'Document_controller@imprimer_en_masse_documents_commerce')->name('imprimer_en_masse_documents_commerce');
            Route::post('fusionner_documents_en_masse', 'Document_controller@fusionner_documents_en_masse')->name('fusionner_documents_en_masse');

            Route::post('envoyer_email_relecture', 'Document_controller@envoyer_email_relecture')->name('envoyer_email_relecture');

            Route::post('mise_a_jour_eco_contribution', 'Document_controller@mise_a_jour_eco_contribution')->name('mise_a_jour_eco_contribution');

            Route::post('facturer_documents/{type_element}', 'Document_controller@facturer_documents')->name('facturer_documents');

            // Documents d'achats
            Route::name('achat.')->prefix('achat')->group(function () {

                Route::post('recuperer_adresse_par_defaut_fournisseur', 'Document_controller@recuperer_adresse_par_defaut_fournisseur')->name('adresse_par_defaut_fournisseur');
                Route::get('infos_fournisseur/{id}', 'Document_controller@infos_fournisseur')->name('infos_fournisseur');
                Route::post('maj_prix_achat_article', 'Document_controller@maj_prix_achat_article')->name('maj_prix_achat_article');
                Route::post('articles_prix_achats_differents', 'Document_controller@articles_prix_achats_differents')->name('articles_prix_achats_differents');

                // Commande achat
                Route::name('commande.')->prefix('commande')->group(function () {

                    Route::post('reception_totale_ligne/{id}', 'Document_controller@commande_achat_recue')->name('reception_totale_ligne');
                    Route::post('reception_partielle_ligne/{id}', 'Document_controller@commande_fournisseur_partiellement_recue')->name('reception_partielle_ligne');
                    Route::get('retourne_lignes_bl_achat/{id}', 'Document_controller@retourne_lignes_bl_achat_pour_commande_achat_ligne')->name('retourne_lignes_bl_achat');
                    Route::post('suppression_manuelle_reliquat', 'Document_controller@suppression_manuelle_reliquat')->name('suppression_manuelle_reliquat');
                });
            });

            // Documents de vente
            Route::name('vente.')->prefix('vente')->group(function () {

                Route::post('recuperer_adresse_par_defaut', 'Document_controller@recuperer_adresse_par_defaut')->name('adresse_par_defaut_client');
                Route::get('infos_client/{id}', 'Document_controller@infos_client')->name('infos_client');
                Route::post('mise_a_jour_adresses', 'Document_controller@mise_a_jour_adresses')->name('mise_a_jour_adresses');

                // Commande vente
                Route::name('commande.')->prefix('commande')->group(function () {

                    Route::get('{id}/commande_fournisseur_realisee', 'Document_controller@commande_fournisseur_realisee')->name('commande_fournisseur_realisee');
                    Route::get('{id}/commande_fournisseur_recue', 'Document_controller@commande_fournisseur_recue')->name('commande_fournisseur_recue');
                    Route::get('{id}/accuse_de_reception_envoye', 'Document_controller@accuse_de_reception_envoye')->name('accuse_de_reception_envoye');
                    Route::get('{id}/annulation_totale', 'Document_controller@annulation_totale')->name('annulation_totale');
                    Route::post('annuler_documents', 'Document_controller@annuler_documents')->name('annuler_documents');

                });

                // Devis vente
                Route::name('devis.')->prefix('devis')->group(function () {

                    Route::post('calcule_date_expiration', 'Document_controller@calcule_date_expiration')->name('calcule_date_expiration');
                    Route::post('commander_documents', 'Document_controller@commander_documents')->name('commander');
                });

                // Facture vente
                Route::name('facture.')->prefix('facture')->group(function () {

                    Route::get('creer_avancement_depuis_projet/{id_projet}', 'Document_controller@creer_facture_avancement_depuis_projet')->name('creer_avancement_depuis_projet');
                    Route::get('{id}/indique_document_comme_envoye', 'Document_controller@indique_document_comme_envoye')->name('indique_document_comme_envoye');
                    Route::post('affacturer_documents', 'Affacturage_controller@affacturer_documents')->name('affacturer_documents');

                });

            });

            Route::name('devis.')->prefix('devis')->group(function () {

                Route::get('{type}/{id}/refuser', 'Document_controller@refuser')->name('refuser');
                Route::get('{type}/{id}/annuler_devis', 'Document_controller@annuler_devis')->name('annuler');
                Route::get('{type}/{id}/mise_en_attente', 'Document_controller@mise_en_attente')->name('mise_en_attente');

            });

            // paiement
            Route::name('paiement.')->prefix('paiement')->group(function () {

                Route::post('ajouter/{type_element}/{id}', 'Document_controller@ajouter_paiement')->name('ajouter');
                Route::post('paiements_non_rattaches/{type_element}/{id}', 'Document_controller@paiements_non_rattaches')->name('paiements_non_rattaches');
                Route::post('ajouter_existant/{type_element}/{id}', 'Document_controller@ajouter_paiement_existant')->name('ajouter_existant');
                Route::get('supprimer/{id}', 'Document_controller@supprimer_paiement')->name('supprimer');
                Route::get('detacher/{id}', 'Document_controller@detacher_paiement')->name('detacher');
            });

            Route::get('{type_element}', 'Document_controller@creer')->name('creer');
            Route::get('{type_element}/avec_element/{champ}/{element_id}', 'Document_controller@creer_avec_element')->name('creer_avec_element');
            Route::get('{type_element}/{id}', 'Document_controller@creer')->name('afficher');
            Route::get('{type_element}/{id}/post_enregistrement', 'Document_controller@creer_post_enregistrement')->name('post_enregistrement');
            Route::get('{type_element}/{id}/post_enregistrement_avec_pdf', 'Document_controller@creer_post_enregistrement_avec_pdf')->name('post_enregistrement_avec_pdf');
            Route::post('{type_element}/enregistrer', 'Document_controller@enregistrer')->name('enregistrer');
            Route::post('{type_element}/test_enregistrer', 'Document_controller@test_enregistrer')->name('test_enregistrer');
            Route::post('{type_element}/document_enregistrer_lignes', 'Document_controller@document_enregistrer_lignes')->name('enregistrer_lignes');
            Route::get('{type_element}/{id}/supprimer', 'Document_controller@supprimer')->name('supprimer');
            Route::get('{type_element}/{id}/valider', 'Document_controller@valider')->name('valider');
            Route::get('{type_element}/{id}/accepter', 'Document_controller@accepter')->name('accepter');
            Route::get('{type_element}/{id}/annuler', 'Document_controller@annuler')->name('annuler');
            Route::get('{type_element}/{id}/spliter', 'Document_controller@spliter')->name('spliter');
            Route::get('{type_element}/{id}/expedier', 'Document_controller@expedier')->name('expedier');
            Route::get('{type_element}/{id}/annule_reglement', 'Document_controller@annule_reglement')->name('annule_reglement');
            Route::get('{type_element}/{id}/valide_reglement', 'Document_controller@valide_reglement')->name('valide_reglement');

            // transformer en un document du même type (vente vers vente ou achat vers achat)
            Route::get('{type_element}/{id}/transformer/{type_element_transformation}', 'Document_controller@transformer')->name('transformer');
            Route::get('{type_element}/{id}/transformer/{type_element_transformation}/{date}', 'Document_controller@transformer')->name('transformer_date');
            Route::get('{type_element}/{id}/transformer/{type_element_transformation}/{date}/{variante}', 'Document_controller@transformer')->name('transformer_variante');

            // transformer un document de vente vers un document d'achat
            Route::get('{type_element}/{id}/transformer_fournisseur/{type_element_transformation}', 'Document_controller@transformer_document_fournisseur')->name('transformer_fournisseur');
            Route::post('{type_element}/{id}/transformer_fournisseur_avec_articles/{type_element_transformation}', 'Document_controller@transformer_document_fournisseur_avec_articles')->name('transformer_fournisseur_avec_articles');

            // Transformer modèle de relance
            Route::post('{type_element}/{id}/transformer_modele_relance', 'Document_controller@transformer_modele_relance')->name('transformer_modele_relance');
            Route::post('{type_element}/{id}/generer_modele_relance', 'Document_controller@generer_modele_relance')->name('generer_modele_relance');

            Route::get('{type_element}/{id}/{modele_doc}/imprimer_modele', 'Document_controller@impression_sur_mesure')->name('modele_imprimer');

            // Relancer les utilisateurs par mail
            Route::post('{type_element}/{id}/relancer_utilisateurs', 'Document_controller@relancer_utilisateurs')->name('relancer_utilisateurs');

            Route::get('{type_element}/{id}/previsualisation_pdf', 'Document_controller@previsualisation_pdf')->name('previsualisation_pdf');

            Route::get('{type_element}/{id}/previsualisation_facturation_electronique', 'Document_controller@previsualisation_facturation_electronique')->name('previsualisation_facturation_electronique');

            Route::get('{type_element}/{id}/mise_a_jour_tags', 'Document_controller@mise_a_jour_tags')->name('mise_a_jour_tags');

            Route::get('{type_element}/{id}/fournisseurs_par_article', 'Document_controller@fournisseurs_par_article')->name('fournisseurs_par_article');
        });

        /*
        |--------------------------------------------------------------------------
        | Gestion des stocks
        |--------------------------------------------------------------------------
        */
        Route::name('stocks.')->prefix('gestion_des_stocks')->group(function () {
            Route::get('', 'Gestion_des_stocks_controller@afficher')->name('afficher');
            Route::post('ajustement_stock', 'Gestion_des_stocks_controller@ajustement_stock')->name('ajustement_stock');
        });

        /*
        |--------------------------------------------------------------------------
        | Comptabilisation
        |--------------------------------------------------------------------------
        */
        Route::name('comptabilisation.')->prefix('compta')->group(function () {
            Route::post('comptabiliser/{type_element}', 'Compta_controller@comptabiliser')->name('execute');
            Route::get('export', 'Compta_controller@export')->name('export');
            Route::post('export', 'Compta_controller@export_post')->name('export_post');
        });

        /*
        |--------------------------------------------------------------------------
        | Accueil ADV
        |--------------------------------------------------------------------------
        */
        Route::name('adv.')->prefix('adv')->group(function () {

            Route::get('', 'Adv_controller@index')->name('index');
            Route::post('recuperer_rapport', 'Adv_controller@recuperer_rapport')->name('recuperer_rapport');
        });

        /*
        |--------------------------------------------------------------------------
        | Trésorerie
        |--------------------------------------------------------------------------
        */
        Route::name('tresorerie.')->prefix('tresorerie')->group(function () {

            Route::post('{type_element}/enregistrer', 'Treso_controller@enregistrer')->name('enregistrer');
            Route::post('{type_element}/supprimer', 'Treso_controller@supprimer')->name('supprimer');
            Route::post('{type_element}/maj_ordre', 'Treso_controller@maj_ordre')->name('maj_ordre');
            Route::post('enregistrer_nombre_de_mois', 'Treso_controller@enregistrer_nombre_de_mois')->name('enregistrer_nombre_de_mois');
            Route::post('enregistrer_affichage_transaction', 'Treso_controller@enregistrer_affichage_transaction')->name('enregistrer_affichage_transaction');
            Route::post('{type_element}/changement_entite', 'Treso_controller@changement_entite')->name('changement_entite');
            Route::post('enregistrer_etat_transaction', 'Treso_controller@enregistrer_etat_transaction')->name('enregistrer_etat_transaction');

            Route::get('{type_element}', 'Treso_controller@affichage')->name('index');
        });

        /*
        |--------------------------------------------------------------------------
        | Bordereau
        |--------------------------------------------------------------------------
        */
        Route::name('bordereau.')->prefix('bordereau')->group(function () {

            Route::get('imprimer_pdf/{id_bordereau}', 'Bordereau_controller@imprimer_pdf')->name('imprimer_pdf');
        });

        /*
        |--------------------------------------------------------------------------
        | Récurrence
        |--------------------------------------------------------------------------
        */
        Route::name('recurrence.')->prefix('recurrence')->group(function () {

            Route::get('stopper/{id}', 'Recurrence_controller@stopper')->name('stopper');
        });

        /*
        |--------------------------------------------------------------------------
        | Coupon de réduction
        |--------------------------------------------------------------------------
        */
        Route::name('coupon_reduction.')->prefix('coupon_reduction')->group(function () {
            Route::post('calcule', 'Document_controller@calcule_coupon_reduction')->name('calcule');

            // afficher le pdf d'un coupon réduction
            Route::get('{id}/afficher_pdf', 'Coupon_reduction_controller@afficher_pdf')->name('pdf_afficher');
        });

        /*
        |--------------------------------------------------------------------------
        | Recouvrement
        |--------------------------------------------------------------------------
        */
        Route::name('recouvrement.')->prefix('recouvrement')->group(function () {
            Route::get('', 'Recouvrement_controller@index')->name('index');
            Route::get('sans_groupe', 'Recouvrement_controller@recouvrement_sans_groupe')->name('sans_groupe');

            Route::post('ajoute_relance', 'Recouvrement_controller@ajoute_relance')->name('ajoute_relance');
            Route::post('supprimer_relance', 'Recouvrement_controller@supprimer_relance')->name('supprimer_relance');
            Route::post('changement_entite', 'Recouvrement_controller@changement_entite_pour_recouvrement')->name('changement_entite');
            Route::post('calcule_indicateurs_par_periode', 'Recouvrement_controller@calcule_indicateurs_par_periode')->name('calcule_indicateurs_par_periode');
        });

        /*
        |--------------------------------------------------------------------------
        | Encaissement CB
        |--------------------------------------------------------------------------
        */
        Route::name('encaissement_cb.')->prefix('encaissement_cb')->group(function () {

            Route::get('', 'Encaissement_cb_controller@index')->name('index');
            Route::post('', 'Encaissement_cb_controller@encaisse')->name('encaisse');
        });

        /*
        |--------------------------------------------------------------------------
        | Prélèvement manuel
        |--------------------------------------------------------------------------
        */
        Route::name('prelevement_manuel.')->prefix('prelevement_manuel')->group(function () {

            Route::get('{client_id}', 'Paiement_controller@prelevement_manuel')->name('index');
            Route::post('', 'Paiement_controller@prelevement_manuel_post')->name('enregistrer');

        });

        /*
       |--------------------------------------------------------------------------
       | Régélements à recevoir
       |--------------------------------------------------------------------------
       */
        Route::name('reglements_a_recevoir.')->prefix('reglements_a_recevoir')->group(function () {

            Route::post('', 'Reglements_controller@reglements_a_recevoir_post')->name('traitement');

        });

        /*
        |--------------------------------------------------------------------------
        | Gestion de l'ecommerce depuis l'ERP
        |--------------------------------------------------------------------------
        */
        Route::name('eden_ecommerce.')->prefix('eden_ecommerce')->group(function () {

            Route::get('client/{id}/envoyer_nouveau_mdp_au_client', 'Client_controller@envoyer_nouveau_mdp_au_client')->name('envoyer_nouveau_mdp_au_client');

        });

        /*
        |--------------------------------------------------------------------------
        | Article catégorie comptable
        |--------------------------------------------------------------------------
        */
        Route::name('article_categorie_comptable.')->prefix('article_categorie_comptable')->group(function () {
            Route::post('{article_categorie_comptable_id}/verification_impacts_documents', 'Article_categorie_comptable_controller@verification_impacts_documents')->name('verification_impacts_documents');

        });

        /*
        |##########################################################################
        | Intégration externe
        |##########################################################################
        */

        /*
        |--------------------------------------------------------------------------
        | Gestion de budget insight
        |--------------------------------------------------------------------------
        */
        Route::name('budget_insight.')->prefix('budget_insight/')->group(function () {

            Route::get('afficher', 'Budget_Insight_controller@afficher')->name('index');
            Route::post('afficher', 'Budget_Insight_controller@afficher')->name('index_post');

            Route::get('mise_a_jour_connexion/{id}', 'Budget_Insight_controller@mise_a_jour_connexion')->name('mise_a_jour_connexion');

            // créer une opération de tréso
            Route::post('saisie_operation_treso', 'Budget_Insight_controller@saisie_operation_treso')->name('saisie_operation_treso');

            // On récupère la liste des factures
            Route::post('recupere_facture_pour_paiement', 'Budget_Insight_controller@recupere_facture_pour_paiement')->name('recupere_facture_pour_paiement');

            // On récupère la liste des commandes
            Route::post('recupere_commande_pour_paiement', 'Budget_Insight_controller@recupere_commande_pour_paiement')->name('recupere_commande_pour_paiement');

            // On récupère la liste des acomptes
            Route::post('recupere_acompte_pour_paiement', 'Budget_Insight_controller@recupere_acompte_pour_paiement')->name('recupere_acompte_pour_paiement');

            // On récupère la liste des avoirs
            Route::post('recupere_avoir_pour_paiement', 'Budget_Insight_controller@recupere_avoir_pour_paiement')->name('recupere_avoir_pour_paiement');

            // On récupère la liste des factures achat
            Route::post('recupere_facture_achat_pour_paiement', 'Budget_Insight_controller@recupere_facture_achat_pour_paiement')->name('recupere_facture_achat_pour_paiement');

            // On récupère la liste des avoirs achat
            Route::post('recupere_avoir_achat_pour_paiement', 'Budget_Insight_controller@recupere_avoir_achat_pour_paiement')->name('recupere_avoir_achat_pour_paiement');

            // On récupère la liste des notes de frais
            Route::post('recupere_note_de_frais_pour_paiement', 'Budget_Insight_controller@recupere_note_de_frais_pour_paiement')->name('recupere_note_de_frais_pour_paiement');

            // route pour enregistrer un paiement
            Route::post('enregistre_paiement/{type_element}', 'Budget_Insight_controller@enregistre_paiement')->name('enregistre_paiement');

            // rapprochement (paiement)
            Route::post('recupere_paiements_non_rapproches', 'Budget_Insight_controller@recupere_paiements_non_rapproches')->name('recupere_paiements_non_rapproches');
            Route::post('enregistre_rapprochement', 'Budget_Insight_controller@enregistre_rapprochement')->name('enregistre_rapprochement');

            // rapprochement (bordereau)
            Route::post('recupere_bordereaux_non_rapproches', 'Budget_Insight_controller@recupere_bordereaux_non_rapproches')->name('recupere_bordereaux_non_rapproches');
            Route::post('enregistre_rapprochement_bordereau', 'Budget_Insight_controller@enregistre_rapprochement_bordereau')->name('enregistre_rapprochement_bordereau');


            // séparation
            Route::post('recupere_paiements', 'Budget_Insight_controller@recupere_paiements')->name('recupere_paiements');
            Route::post('separe_transaction', 'Budget_Insight_controller@separe_transaction')->name('separe_transaction');

            // reporter une opération
            Route::post('reporter', 'Budget_Insight_controller@reporter')->name('reporter');

            // paramétrage de la synchro
            Route::get('parametrage_synchro', 'Budget_Insight_controller@parametrage_synchro')->name('parametrage_synchro');

            // récupération des infos d'un fournisseur via les automatisations
            Route::get('informations_pour_fournisseur/{id}', 'Budget_Insight_controller@informations_pour_fournisseur')->name('informations_pour_fournisseur');

            Route::get('synchronisation', 'Budget_Insight_controller@synchronisation')->name('synchro');

        });

        /*
        |--------------------------------------------------------------------------
        | Docusign
        |--------------------------------------------------------------------------
        */
        Route::name('docusign.')->prefix('docusign')->group(function () {

            Route::get('droit_docusign_api/{compte_individuel?}', 'Signature_controller@droit_docusign_api')->name('droit_docusign_api');
            Route::get('retour_droit_docusign_api', 'Signature_controller@retour_droit_docusign_api')->name('retour_droit_docusign_api');
            Route::get('informations_fichiers/{type_element}/{element_id}', 'Signature_controller@informations_fichiers')->name('informations_fichiers');
            Route::post('relance_signature/{element_id}', 'Signature_controller@relance_signature')->name('relance_signature');
        });

        /*
        |--------------------------------------------------------------------------
        | Mfiles
        |--------------------------------------------------------------------------
        */
        Route::name('mfiles.')->prefix('mfiles')->group(function () {

            Route::get('recuperer_classes/{recuperer_documents?}', 'Mfiles_controller@recuperer_classes')->name('recuperer_classes');
            Route::get('recuperer_attributs', 'Mfiles_controller@recuperer_attributs')->name('recuperer_attributs');
            Route::post('recuperer_jeton_authentification', 'Mfiles_controller@connexion_mfiles')->name('recuperer_jeton_authentification');
            Route::get('document/{type_element}/{id_element}/envoi_mfiles', 'Mfiles_controller@envoi_document_mfiles')->name('envoi_document_mfiles');

        });

        /*
        |--------------------------------------------------------------------------
        | API interne
        |--------------------------------------------------------------------------
        */
        Route::name('api_interne.')->prefix('api_interne')->group(function () {

            Route::post('recuperer_siret', 'Siret_controller@recuperer_entreprise')->name('recuperer_entreprise');

        });

        /*
        |--------------------------------------------------------------------------
        | Microsoft
        |--------------------------------------------------------------------------
        */
        Route::name('microsoft.')->prefix('microsoft')->group(function () {

            Route::get('dossiers_boite_mail/{adresse_email}', 'Integrations\Microsoft_controller@dossiers_boite_mail')->name('dossiers_boite_mail');
            Route::get('alias_email/{utilisateur_id}', 'Integrations\Microsoft_controller@alias_email')->name('alias_email');

        });

        /*
        |--------------------------------------------------------------------------
        | Transformation document des temps
        |--------------------------------------------------------------------------
        */
        Route::name('transformation_document_temps.')->prefix('transformation_document_temps')->group(function () {
            Route::post('calcul_utilisateurs_concernes', 'Transformation_document_temps_controller@calcul_utilisateurs_concernes')->name('calcul_utilisateurs_concernes');
            Route::get('transformation', 'Transformation_document_temps_controller@transformation')->name('transformation');
        });

        /*
        |--------------------------------------------------------------------------
        | Gestion adresse
        |--------------------------------------------------------------------------
        */
        Route::name('adresse.')->prefix('adresse')->group(function () {
            Route::post('chargement_adresse', 'Adresse_controller@chargement_adresse')->name('chargement_adresse');
            Route::post('detail_adresse', 'Adresse_controller@detail_adresse')->name('detail_adresse');
        });

        /*
        |--------------------------------------------------------------------------
        | Recherche globale sur l'Erp
        |--------------------------------------------------------------------------
        */
        Route::name('recherche.')->prefix('recherche')->group(function () {
            Route::post('', 'Recherche_controller@resultats_recherche')->name('globale');
            Route::post('dedoublonnage/{type_element}', 'Recherche_controller@dedoublonnage')->name('dedoublonnage');
        });

        /*
        |--------------------------------------------------------------------------
        | Synchronisation service
        |--------------------------------------------------------------------------
        */
        Route::name('synchronisation_service.')->prefix('synchronisation_service')->group(function () {
            Route::post('tables_externes/{id}', 'Synchronisation_service_controller@tables_externes')->name('tables_externes');
            Route::post('table_externe/{id}/{nom_table}', 'Synchronisation_service_controller@table_externe')->name('table_externe');
            Route::post('champs_externes/{id}', 'Synchronisation_service_controller@champs_externes')->name('champs_externes');
            Route::post('lancer/{id}', 'Synchronisation_service_controller@lancer')->name('lancer');
            Route::post('parametres/{id}', 'Synchronisation_service_controller@parametres')->name('parametres');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Questionnaire
    |--------------------------------------------------------------------------
    */
    Route::name('questionnaire.')->prefix('questionnaire/')->group(function () {

        Route::post('traitement', 'Questionnaire_controller@traitement_reponse')->name('traitement_reponse');
        Route::get('merci', 'Questionnaire_controller@merci')->name('remerciement');
        Route::get('{questionnaire_id}/{repondant_id}/{token}', 'Questionnaire_controller@afficher')->name('affichage');
        Route::get('{questionnaire_id}/prepare_questionnaire', 'Questionnaire_controller@prepare_questionnaire')->name('preparation');

        Route::middleware(['eden_utilisateur_connecte', 'eden_middleware'])->group(function () {

            Route::post('{questionnaire_id}/enregistrer', 'Questionnaire_controller@enregistrer')->name('enregistrer');
            Route::post('envoi_questionnaire', 'Questionnaire_controller@envoi_questionnaire')->name('envoi');
            Route::get('{questionnaire_id}/reponses', 'Questionnaire_controller@reponses')->name('reponses');

            Route::get('{id_questionnaire}/export', 'Export_controller@exporter_reponses_questionnaire')->name('export');

        });
    });

    /*
    |--------------------------------------------------------------------------
    | Addon messagerie
    |--------------------------------------------------------------------------
    */
    Route::prefix('addon_messagerie')->group(function () {

        Route::post('authentification', 'Addon_messagerie_controller@identification_utilisateur');
        Route::post('recuperation_champs', 'Addon_messagerie_controller@recuperation_champs_obligatoires');
        Route::post('recuperation_valeurs_liste', 'Addon_messagerie_controller@recuperation_valeurs_liste');
        Route::post('client', 'Addon_messagerie_controller@recuperation_client');
        Route::post('projet', 'Addon_messagerie_controller@recuperation_projet');
        Route::post('contact', 'Addon_messagerie_controller@recuperation_contact');
        Route::post('ajout_contact', 'Addon_messagerie_controller@ajout_contact');
        Route::post('ajout_client', 'Addon_messagerie_controller@ajout_client');
        Route::post('recherche_contact', 'Addon_messagerie_controller@recherche_contact');
        Route::post('recherche_client', 'Addon_messagerie_controller@recherche_client');
        Route::post('maj_contact', 'Addon_messagerie_controller@maj_contact');
        Route::post('stock_mail', 'Addon_messagerie_controller@stock_mail');
        Route::post('recupere_clients', 'Addon_messagerie_controller@recupere_clients');

    });

    /*
    |--------------------------------------------------------------------------
    | Google
    |--------------------------------------------------------------------------
    */
    Route::name('google.')->prefix('google')->group(function () {
        Route::post('geocodage_position', 'Api\Google_controller@geocodage_position')->name('geocodage_position');
    });

    /*
    |--------------------------------------------------------------------------
    | AI
    |--------------------------------------------------------------------------
    */
    Route::name('ai.')->prefix('ai')->group(function () {
        Route::post('requete_open_ai/{cle_contexte}', 'Api\Ai_controller@requete_open_ai')->name('requete_open_ai');
    });


    //Version
    Route::get('version_actuelle', 'Version_controller@version_actuelle')->name('version_actuelle');

    // bibliothèque, publique, avec token
    Route::get('bibliotheque/afficher/{type_element}/{nom_sql}/{id_element}/{token?}', 'Bibliotheque_controller@afficher_fichier_via_champ_libre')->name('bibliotheque_url_publique');

    // récupération des pièces jointes hors connexion
    Route::get('piece_jointe/recuperer/{id}/{token}', 'Piece_jointe_controller@recuperer_hors_connexion')->name('piece_jointe_recuperer_hors_connexion');

});

//url raccourcies
Route::get('/lr/{caractere}', 'Eden\Controllers\Lien_controller@redirection');

Route::name('form.')->namespace('Eden\Controllers')->middleware(['frame'])->group(function () {

    Route::get('/form/{id_form}', 'Formulaire_controller@affichage_web')->name('index');
    Route::post('/valid_form/{id_form}', 'Formulaire_controller@valider_formulaire_web')->name('post');

});


/*
|--------------------------------------------------------------------------
| Interface fournisseur
|--------------------------------------------------------------------------
*/
Route::name('interface_fournisseur.')->prefix('interface_fournisseur')->group(function () {

    Route::get('erreur_acces', 'Eden\Controllers\Interface_fournisseur_controller@erreur_acces')->name('erreur_acces');

    Route::middleware(['eden_fournisseur_connecte'])->group(function () {
        Route::get('{id_fournisseur}/{clef_fournisseur_hachee}/accueil', 'Eden\Controllers\Interface_fournisseur_controller@accueil')->name('accueil');
        Route::post('{clef_fournisseur_hachee}/facture_achat_enregistre', 'Eden\Controllers\Interface_fournisseur_controller@enregistre_facture_achat')->name('enregistre_facture_achat');
    });

});

Route::name('email.')->prefix('eden/email')->group(function () {

    // Retourne pièce jointe par email
    Route::get('image_signature/{id_compte_email}', 'Eden\Controllers\Email_controller@afficher_image_signature')->name('image_signature');

});
