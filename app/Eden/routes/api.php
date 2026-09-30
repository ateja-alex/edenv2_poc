<?php

Route::middleware(['api_eden'])->prefix('api')->group(function() {

    Route::post('{type_element}/list', 'Eden\Controllers\Api\Api_controller@list');
    Route::get('{type_element}/list', 'Eden\Controllers\Api\Api_controller@list');

    Route::post('{type_element}/save', 'Eden\Controllers\Api\Api_controller@save');

    Route::post('{type_element}/save/{id_element}', 'Eden\Controllers\Api\Api_controller@save');

    Route::post('{type_element}/get/{id_element}', 'Eden\Controllers\Api\Api_controller@get');
    Route::get('{type_element}/get/{id_element}', 'Eden\Controllers\Api\Api_controller@get');

    Route::post('{type_element}/delete/{id_element}', 'Eden\Controllers\Api\Api_controller@delete');

    // validation d'un document en gestion commerciale
    Route::post('{type_element}/valid/{id_element}', 'Eden\Controllers\Api\Api_controller@valid');

    // Transformaiton d'un document
    Route::post('{type_element}/transform/{id_element}/{type_transformation}', 'Eden\Controllers\Api\Api_controller@transform_document');

    // Mise à jour des tarifs d'un document
    Route::post('{type_element}/update_price/{id_element}', 'Eden\Controllers\Api\Api_controller@update_price');

    // envoi d'un email
    Route::post('email', 'Eden\Controllers\Api\Api_controller@email_modele_eden');


    // récupération de paramètres
    Route::post('settings', 'Eden\Controllers\Api\Api_controller@settings');

    //Routes Contact
    Route::get('contact/telephone/{numero_telephone}', 'Eden\Controllers\Api\Api_controller@recupere_contacts_avec_telephone');
    Route::post('contact/telephone/{numero_telephone}', 'Eden\Controllers\Api\Api_controller@recupere_contacts_avec_telephone');
    Route::get('contact/email/{email}', 'Eden\Controllers\Api\Api_controller@recupere_contacts_avec_email');
    Route::post('contact/email/{email}', 'Eden\Controllers\Api\Api_controller@recupere_contacts_avec_email');

    //Récupération du nombre d'utilisateurs par type
    Route::get('utilisateurs', 'Eden\Controllers\Api\Api_controller@recupere_utilisateurs_par_type');

    Route::post('ajout_traductions', 'Eden\Controllers\Api\Api_controller@ajout_traductions');
    Route::post('ajout_traductions_synchronisation', 'Eden\Controllers\Api\Api_controller@ajout_traductions_synchronisation');

    Route::post('maj_utilisateurs_projet', 'Eden\Controllers\Api\Api_controller@maj_utilisateurs_projet');
    Route::post('maj_licences_projet', 'Eden\Controllers\Api\Api_controller@maj_licences_projet');
    Route::post('maj_jour_indisponibilites', 'Eden\Controllers\Api\Api_controller@maj_jour_indisponibilites');

    Route::post('envoyer_mail', 'Eden\Controllers\Api\Api_controller@envoyer_mail');

    Route::post('modification-ticket', 'Eden\Controllers\Synchro_suivi_recette_controller@recois_modifications_ticket');
    Route::post('echange-ticket', 'Eden\Controllers\Synchro_suivi_recette_controller@recois_ticket_echange');

    Route::post('synchronisation_service/{id_element}', 'Eden\Controllers\Api\Api_controller@synchronisation_service')->name('synchronisation_service');
});