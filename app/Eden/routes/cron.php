<?php

/**
 * Tâches Cron
 */
Route::name('eden_cron.')->prefix('eden/cron')->group(function() {

    Route::get('demander_infos_paiement_emails', 'Eden\Controllers\Cron_controller@demander_infos_paiement_emails')->name('demander_infos_paiement_emails');
    Route::get('envoyer_emails', 'Eden\Controllers\Cron_controller@envoyer_emails')->name('envoyer_emails');
    Route::get('changements_tarifs', 'Eden\Controllers\Cron_controller@changements_tarifs')->name('changements_tarifs');
    Route::get('genere_interventions_maintenance', 'Eden\Controllers\Cron_controller@genere_interventions_maintenance')->name('genere_interventions_maintenance');
    Route::get('envoyer_relances', 'Eden\Controllers\Cron_controller@envoyer_relances')->name('envoyer_relances');
    Route::get('seuil-stocks-articles', 'Eden\Controllers\Cron_controller@seuil_stocks_articles')->name('seuil_stocks_articles');
    Route::get('taux_de_charge_actuel', 'Eden\Controllers\Cron_controller@taux_de_charge_actuel')->name('taux_de_charge_actuel');
    Route::get('notifications_emails', 'Eden\Controllers\Cron_controller@notifications_emails')->name('notifications_emails');
    Route::get('execute_requete_sql_cron', 'Eden\Controllers\Cron_controller@execute_requete_sql_cron')->name('execute_requete_sql_cron');
    Route::get('envoi_mails_questionnaires', 'Eden\Controllers\Cron_controller@envoi_mails_questionnaires')->name('envoi_mails_questionnaires');
    Route::get('comparer_prix_ventes_aux_evolutions/{id?}', 'Eden\Controllers\Cron_controller@comparer_prix_ventes_aux_evolutions')->name('comparer_prix_ventes_aux_evolutions');
    Route::get('generation_elements_recurrents', 'Eden\Controllers\Cron_controller@generation_elements_recurrents')->name('generation_elements_recurrents');
    Route::get('maj_chaines_tags_recherche/{types_elements?}', 'Eden\Controllers\Cron_controller@maj_chaines_tags_recherche')->name('maj_chaines_tags_recherche');
    Route::get('maj_chaines_affichage/{types_elements?}', 'Eden\Controllers\Cron_controller@maj_chaines_affichage')->name('maj_chaines_affichage');
    Route::get('maj_droits_affichage_index/{types_elements?}', 'Eden\Controllers\Cron_controller@maj_droits_affichage_index')->name('maj_droits_affichage_index');
    Route::get('generer_pdfs_documents', 'Eden\Controllers\Cron_controller@generer_pdfs_documents')->name('generer_pdfs_documents');
    Route::get('compresser_images', 'Eden\Controllers\Cron_controller@compresser_images')->name('compresser_images');
    Route::get('synchroniser_rdv_microsoft', 'Eden\Controllers\Cron_controller@synchroniser_rdv_microsoft')->name('synchroniser_rdv_microsoft');
    Route::get('synchroniser_rdv_microsoft_echoues', 'Eden\Controllers\Cron_controller@synchroniser_rdv_microsoft_echoues')->name('synchroniser_rdv_microsoft_echoues');
    Route::get('synchroniser_rdv_google', 'Eden\Controllers\Cron_controller@synchroniser_rdv_google')->name('synchroniser_rdv_google');
    Route::get('synchroniser_rdv_google_echoues', 'Eden\Controllers\Cron_controller@synchroniser_rdv_google_echoues')->name('synchroniser_rdv_google_echoues');
    Route::get('supprimer_doublons_rdv_microsoft', 'Eden\Controllers\Cron_controller@supprimer_doublons_rdv_microsoft')->name('supprimer_doublons_rdv_microsoft');
    Route::get('relever_tickets_support', 'Eden\Controllers\Cron_controller@relever_tickets_support')->name('relever_tickets_support');
    Route::get('relever_integration_email', 'Eden\Controllers\Cron_controller@relever_integration_email')->name('relever_integration_email');
    Route::get('import_sur_mesure', 'Eden\Controllers\Cron_controller@import_sur_mesure')->name('import_sur_mesure');
    Route::get('synchronisation_mfiles/{forcer_modification?}', 'Eden\Controllers\Cron_controller@synchronisation_mfiles')->name('synchronisation_mfiles');
    Route::get('synchronisation_budget_insight', 'Eden\Controllers\Cron_controller@synchronisation_budget_insight')->name('synchronisation_budget_insight');
    Route::get('dedoublonnage_budget_insight', 'Eden\Controllers\Cron_controller@dedoublonnage_budget_insight')->name('dedoublonnage_budget_insight');
    Route::get('notification_manuelle/gestion_des_notifications_manuelles/{notification_id?}', 'Eden\Controllers\Cron_controller@gestion_des_notifications_manuelles')->name('notification_manuelle.gestion_des_notifications_manuelles');
    Route::get('mail', 'Eden\Controllers\Cron_controller@synchroniser_emails')->name('afficher_mails');
    Route::get('mails/fournisseurs', 'Eden\Controllers\Mail_controller@synchro_boite_reception_fournisseurs')->name('boite_reception_fournisseurs');
    Route::get('suppression_emails_inutiles', 'Eden\Controllers\Cron_controller@suppression_emails_inutiles')->name('suppression_emails_inutiles');
    Route::get('mise_a_jour_index_recherche_element', 'Eden\Controllers\Cron_controller@mise_a_jour_index_recherche_element')->name('mise_a_jour_index_recherche_element');
    Route::get('recuperation_enveloppes_docusign', 'Eden\Controllers\Cron_controller@recuperation_enveloppes_docusign')->name('recuperation_enveloppes_docusign');
    Route::get('forcer_synchro_banque_budget_insight', 'Eden\Controllers\Cron_controller@forcer_synchro_banque_budget_insight')->name('forcer_synchro_banque_budget_insight');
    Route::get('optimisation_tables', 'Eden\Controllers\Cron_controller@optimisation_tables')->name('optimisation_tables');
    Route::get('creation_occurrences_recurrence', 'Eden\Controllers\Cron_controller@creation_occurrences_recurrence')->name('creation_occurrences_recurrence');
    Route::get('synchronisation_jour_indisponibilite/{pays?}', 'Eden\Controllers\Cron_controller@synchronisation_jour_indisponibilite')->name('synchronisation_jour_indisponibilite');
    Route::get('supprimer_export_differe', 'Eden\Controllers\Cron_controller@supprimer_export_differe');
    Route::get('synchronisation_service/{synchronisation_service_id}', 'Eden\Controllers\Cron_controller@synchronisation_service')->name('synchronisation_service');
    Route::get('synchronisation_service_groupe/{synchronisation_service_id}', 'Eden\Controllers\Cron_controller@synchronisation_service_groupe')->name('synchronisation_service_groupe');
});
