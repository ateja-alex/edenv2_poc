<?php

/**
 * Routes e-commerce
 */

Route::name('ecommerce.')->namespace('Eden\Controllers\Ecommerce')->group(function() {

    // Page d'accueil
    Route::get('/', 'Accueil_controller@afficher_accueil')->name('accueil');
    Route::get('/inscription', 'Mon_compte_controller@inscription')->name('inscription');
    Route::post('/inscription', 'Mon_compte_controller@inscription_post')->name('inscription');

    Route::get('/connexion', 'Mon_compte_controller@connexion')->name('connexion');
    Route::get('/connexion/panier', 'Mon_compte_controller@connexion_panier')->name('connexion.panier');
    Route::post('/connexion', 'Mon_compte_controller@connexion_post')->name('connexion_post');
    Route::post('/connexion/panier', 'Mon_compte_controller@connexion_panier_post')->name('connexion.panier_post');
    Route::post('/connexion-json', 'Mon_compte_controller@connexion_post_json')->name('connexion_post_json');
    Route::get('/deconnexion', 'Mon_compte_controller@deconnexion')->name('deconnexion');

    Route::get('/mot-de-passe-oublie', 'Mon_compte_controller@mot_de_passe_oublie')->name('mot_de_passe_oublie');
    Route::post('/mot-de_passe_oublie', 'Mon_compte_controller@mot_de_passe_oublie_envoi_mail')->name('mot_de_passe_oublie_store');
    Route::post('/mot-de_passe_oublie_ajax', 'Mon_compte_controller@mot_de_passe_oublie_envoi_mail_ajax')->name('mot_de_passe_oublie_store_ajax');
    Route::get('/reinitialisation-mot-de-passe/{id}/{token}', 'Mon_compte_controller@reinitialisation_mot_de_passe')->name('reinitialisation_mot_de_passe');
    Route::post('/reinitialisation-mot-de-passe', 'Mon_compte_controller@reinitialisation_mot_de_passe_post')->name('reinitialisation_mot_de_passe_post');
    Route::post('/reinitialisation_mot_de_passe_post_ajax', 'Mon_compte_controller@reinitialisation_mot_de_passe_post_ajax')->name('reinitialisation_mot_de_passe_post_ajax');

    // la partie ecommerce / mon compte
    Route::middleware(['ecommerce_utilisateur_connecte'])->prefix('mon-compte')->group(function () {

        Route::get('', 'Mon_compte_controller@mon_compte')->name('mon_compte');
        Route::get('informations-personnelles', 'Mon_compte_controller@informations_personnelles')->name('informations_personnelles');
        Route::post('informations-personnelles', 'Mon_compte_controller@informations_personnelles_post')->name('informations_personnelles');
        Route::post('informations-personnelles-ajax', 'Mon_compte_controller@informations_personnelles_post_ajax')->name('informations_personnelles_ajax');
        Route::get('adresses', 'Mon_compte_controller@adresses')->name('adresses');
        Route::post('adresses', 'Mon_compte_controller@adresses_post');
        Route::get('adresse/supprimer/{id}', 'Mon_compte_controller@supprimer_adresse')->name('supprimer_adresse');

        Route::get('historique-commandes', 'Mon_compte_controller@historique_commandes')->name('historique_commandes');

        Route::get('commandes/{id}', 'Mon_compte_controller@afficher_commande')->name('afficher_commande');
        Route::get('commandes/{id}/pdf', 'Mon_compte_controller@telecharger_facture')->name('telecharger_facture');

    });

    Route::prefix('commande')->group(function () {

        // flux de commande
        Route::get('panier', 'Commande_controller@panier')->name('commande.panier');
        Route::get('valide', 'Commande_controller@valide')->name('commande.valide');

        Route::post('panier/modifie_code_postal_pays', 'Panier_controller@modifie_code_postal_pays')->name('panier.code_postal_pays');


        // validation de la commande
        Route::get('confirmation', 'Panier_controller@confirmation_commande')->name('panier.confirmation_commande');

    });

    Route::prefix('panier')->group(function () {

        // Routes panier
        Route::get('', 'Panier_controller@panier')->name('panier');
        Route::get('adresse', 'Panier_controller@affiche_page_adresse')->name('adresse');
        Route::post('adresse', 'Panier_controller@enregistre_adresse')->name('enregistre_adresse');

        Route::get('paiement', 'Panier_controller@affiche_page_paiement')->name('paiement');
        Route::post('paiement', 'Panier_controller@paiement')->name('paiement');

        Route::get('valide', 'Panier_controller@valide')->name('panier.valide');

        // permet d'ajouter un produit au panier
        Route::post('ajout_produit', 'Panier_controller@ajoute_produit_au_panier')->name('ajoute_produit_au_panier');
        Route::post('ajout_produit_ajax', 'Panier_controller@ajax_ajoute_produit_au_panier')->name('ajax_ajoute_produit_au_panier');
        Route::post('modifier_produit_ajax', 'Panier_controller@ajax_modifie_produit_au_panier')->name('ajax_modifie_produit_au_panier');
        Route::get('vider', 'Panier_controller@vider_panier')->name('vider_panier');
        Route::get('retrouver/{client_id}/{panier_enregistre_id}', 'Panier_controller@retrouver_panier')->name('retrouver_panier');

    });


    // les familles et les articles pour le ecommerce, avec url variable
    Route::get('/boutique/{url}', 'Ecommerce_controller@trouve_destination')->where('url', '(.*)')->name('url_ecommerce');
    Route::post('/boutique/{url}', 'Ecommerce_controller@trouve_destination')->where('url', '(.*)')->name('url_ecommerce');

    // Récupération PDF coté site e-commerce
    Route::get('/eden/pdf/{type_element}/{id_element}/{token}', 'Pdf_controller@Recuperation_pdf_pour_client')->name('pdf.recuperation_pdf_pour_client');

    // Contact
    Route::get('/contact', 'Contact_controller@index')->name('contact');
    Route::post('/contact', 'Contact_controller@contact_post')->name('contact_store');

    // Recherche ecommerce
    Route::get('/recherche', 'Recherche_controller@effectuer_recherche')->name('recherche_get');

});


// Gestion du blog
Route::name('blog.')->prefix('blog')->namespace('Eden\Controllers\Blog')->group(function() {
    Route::get('', 'Blog_controller@index')->name('index');
    Route::get('{url}', 'Blog_controller@trouve_destination')->where('url', '(.*)')->name('url_blog');
});

Route::prefix('eden/')->namespace('Eden\Controllers')->group(function() {

// Paiement
    Route::get('mise_a_jour_cb/{plateforme}/{client_id}/{token}', 'Paiement_controller@maj_carte')->name('maj_carte');
    Route::post('mise_a_jour_cb/{plateforme}/{client_id}/{token}', 'Paiement_controller@maj_carte_post')->name('maj_carte_post');
    Route::get('mise_a_jour_cb/payzen/notification_token', 'Paiement_controller@maj_carte_notification_token_payzen')->name('maj_carte_notification_token');
    Route::post('mise_a_jour_cb/payzen/notification_token', 'Paiement_controller@maj_carte_notification_token_payzen')->name('maj_carte_notification_token');

    Route::get('payzen/paiement_solde/{client_id}/{token}', 'Paiement_controller@paiement_solde_payzen')->name('paiement_solde_payzen');
    Route::get('payzen/paiement_solde/{client_id}/{token}/{montant}', 'Paiement_controller@paiement_prelevenement_manuel_payzen')->name('paiement_prelevenement_manuel_payzen');

    Route::get('mise_a_jour_mandat/{plateforme}/{client_id}/{token}', 'Paiement_controller@mise_a_jour_mandat')->name('maj_mandat');
    Route::post('mise_a_jour_mandat/{plateforme}/{client_id}/{token}', 'Paiement_controller@mise_a_jour_mandat_post')->name('maj_mandat_post');

// Interface de paiement Payzen
    Route::get('interface_paiement_factures_payzen', 'Paiement_controller@interface_paiement_factures_payzen')->name('interface_paiement_factures_payzen');
    Route::get('paiement_factures_payzen', 'Paiement_controller@paiement_factures_payzen')->name('paiement_factures_payzen');

// Interface de paiement Payline
    Route::get('paiement_factures_payline', 'Paiement_controller@paiement_factures_payline')->name('paiement_factures_payline');
    Route::get('interface_paiement_factures_payline', 'Paiement_controller@interface_paiement_factures_payline')->name('interface_paiement_factures_payline');

// pages de paiement par CB d'une facture, token = md5('eden' . $facture_id)
    Route::get('paiement_facture/{plateforme}/{facture_id}/{token}', 'Paiement_controller@paiement_facture')->name('paiement_facture');
    Route::post('paiement_facture/{plateforme}/{facture_id}/{token}', 'Paiement_controller@paiement_facture_post')->name('paiement_facture_post');

// différents retours des pages de paiement des factures (annulation, notification, etc)
    Route::get('paiement/{plateforme}/{facture_id}/retour', 'Paiement_controller@retour_paiement')->name('retour_paiement');
    Route::get('paiement/{plateforme}/{facture_id}/annulation', 'Paiement_controller@retour_paiement_annulation')->name('retour_paiement_annulation');
    Route::get('paiement/{plateforme}/{facture_id}/notification', 'Paiement_controller@retour_paiement_notification')->name('retour_paiement_notification');
    Route::get('afficher_pdf/paiement/{facture_id}/{token}', 'Paiement_controller@afficher_pdf')->name('paiement_afficher_pdf');

// Interface Lead
    Route::get('/interface_lead/rsvp/{id}/oui', 'Lead_controller@rsvp_oui')->name('interface_lead.rsvp_oui');
    Route::get('/interface_lead/rsvp/{id}/non', 'Lead_controller@rsvp_non')->name('interface_lead.rsvp_non');
    Route::get('/interface_lead/rsvp/{id}/plustard', 'Lead_controller@rsvp_plustard')->name('interface_lead.rsvp_plustard');
    Route::post('/interface_lead/rsvp', 'Lead_controller@rsvp_maj_reponse')->name('interface_lead.rsvp_maj_reponse');

//Stripe
// Route::get('eden/stripe/test_creation_client', 'Stripe_controller@test_creation_client');
    Route::get('stripe/test_saisie_carte', 'Stripe_controller@test_saisie_carte');
    Route::get('stripe/modification_client/{customer}/{payment_method}', 'Stripe_controller@modification_client');
    Route::get('stripe/test_paiement_client/{customer}/{payment_method}', 'Stripe_controller@test_paiement_client');
    Route::get('stripe/test_liste_payment_methods/{customer}', 'Stripe_controller@test_liste_payment_methods');

    Route::get('mise_a_jour_cb/stripe/{client_id}/{token}', 'Stripe_controller@ajouter_token_stripe')->name('ajouter_token_stripe');
    Route::post('mise_a_jour_cb/stripe/{client_id}/{token}', 'Stripe_controller@maj_token_stripe')->name('maj_token_stripe');
    Route::get('paiement_facture/stripe/{facture_id}/{token}', 'Stripe_controller@paiement_facture')->name('paiement_facture');
    Route::post('paiement_facture/stripe/{facture_id}/{token}', 'Stripe_controller@paiement_facture_post')->name('paiement_facture_post');
    Route::get('afficher_pdf/stripe/{facture_id}/{token}', 'Stripe_controller@afficher_pdf')->name('stripe_afficher_pdf');

});