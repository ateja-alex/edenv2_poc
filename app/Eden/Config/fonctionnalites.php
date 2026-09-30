<?php

use App\Eden\Variables;

$specifique = [];
$script_regroupement_fonctionnalite_ok = false;

if(file_exists(storage_path('app/eden_fonctionnalites.php'))) {

    $specifique = include(storage_path('app/eden_fonctionnalites.php'));

    if (isset($specifique['gescom_document']))
        $script_regroupement_fonctionnalite_ok = true;
}

$documents_gescom_numero_de_serie = array();
$documents_verification_prix_vente_prix_achat = array();
$documents_ecart_gestion_ttc = array();

foreach(Variables::$documents_gescom as $document){

    $documents_gescom_numero_de_serie[$document] = true;
    $type_document_generer_pdf[$document] = true;
    $selection_pj_article_envoie_mail_documents_gescom[$document] = true;
    $document_affichage_option_ajout_lot[$document] = true;
    $document_affichage_option_replacement_article[$document] = false;

    $document_versionning_document[$document] = false;
    $valider_document_enregistrement[$document] = false;
    $documents_ecart_gestion_ttc[$document] = false;

    if(strpos($document,'_vente') !== false) {
        $document_bloquer_pdf_post_validation[$document] = in_array($document, array('facture_vente', 'acompte_vente', 'avoir_vente'));
        $documents_verification_prix_vente_prix_achat[$document] = true;
    }
}

$documents_ecart_gestion_ttc['note_de_frais'] = false;

$standard =  [

    'utiliser_historique_navbar' => true,
    'recherche_par_defaut' => 'tout',
    'projets' => true,
    'taches' => true,
    'recouvrement' => true,


	// si la facture a été relancée il y a moins de x jours, alors elle n'apparait pas dans le tableau
    'recouvrement_ne_pas_afficher_les_factures_relancees_depuis_x_jours' => 0,

    'mode_de_vente' => 'B2C',
    'theme_de_filtres' => false,
    'theme_de_filtres_fournisseur' => false,
	'projet_participants' => false,
	'gescom_commande_vente_annulable_non_supprimable' => 0,

    'modification_document_valide' => [
        'devis_vente' => true,
        'devis_achat' => true,
        'facture_vente' => false,
        'facture_achat' => false,
        'bl_vente' => true,
        'bl_achat' => true,
        'acompte_vente' => false,
        'acompte_achat' => false,
        'avoir_vente' => false,
        'avoir_achat' => false,
        'commande_vente' => true,
        'commande_achat' => true,
        'bon_retour_vente' => true,
        'bon_retour_achat' => true,
        'bon_preparation_vente' => true,
    ],

    'modification_total_hors_taxe_article' => false,

	// les ventes
    'valider_devis_enregistrement' => false,
    'valider_commande_enregistrement' => false,
    'valider_bl_vente_enregistrement' => false,
    'valider_avoir_achat_enregistrement' => false,
    'modification_prix_unitaire_vente' => false,

	// les achats
    'valider_facture_achat_enregistrement' => false,

    'valider_document_enregistrement' => $valider_document_enregistrement,
    'gescom_garder_valeur_saisie_des_articles' => false,

    // bibliothéque
    'gestion_droit_entites_repertoire' => false,

	// extranet
    'utiliser_extranet' => false,
    'url_extranet' => 'extranet',
    'page_accueil_extranet' => '',
    'maquette_extranet' => '',
    'extranet_modele_email_mdp_oublie' => '',
    'extranet_compte_email' => null,

    'bloquer_validation_bl_si_stock_negatif' => false,
    'entrepot_id_par_defaut' => false,

    'bloquer_modification_code_article' => false,
    'copier_prix_achat_pas_conditionnement_article' => true,
    'bloquer_doublon_code_article' => true,
    'bloquer_doublon_reference_article_fournisseur' => true,
    'supprimer_prix_achat_article_fournisseur_prioritaire' => false,
	'comptabiliser_automatiquement' => array(

		'paiement' => false,
		'facture_vente' => false,
		'avoir_vente' => false,
        'facture_achat' => false,
        'avoir_achat' => false,

	),
    'gestion_pieces_jointes' => true,
    'gestion_pieces_jointes_titre' => true,
    'gestion_pieces_jointes_nom' => true,
    'gestion_pieces_jointes_poids' => true,
    'gestion_pieces_jointes_extension' => true,
    'pieces_jointes_garder_nom_originel' => false,
    'compression_images_taille_maximale_en_mo' => 5,
    'pieces_jointes_documents_commerciaux_disponibles' => [

        'devis_vente' => true,
        'commande_vente' => true,
        'bon_preparation_vente' => true,
        'bl_vente' => true,
        'bon_retour_vente' => true,
        'acompte_vente' => true,
        'facture_vente' => true,
        'avoir_vente' => true,
    ],
    'gescom_preselection_articles_sur_document' => false,
    'gescom_mode_preselection_articles_sur_document' => 'classique',
    'gescom_autoriser_document_negatif' => 'bloquant',
    'gescom_mode_selection_articles_via_fournisseur' => false,
    'gescom_cacher_saisie_article_generale' => false,
    'gescom_verification_prix_vente_inferieur_prix_achat' => $documents_verification_prix_vente_prix_achat,
    'gescom_prix_net_modifiable' => false,
    'gescom_validation_quantite_article_nulle' => true,
    'recherche_article_document_gescom_avec_image' => false,
    'option_mettre_a_jour_nomenclatures_et_tarifs' => false,
    'regroupement_articles_documents' => false,

    'couleur_regroupement_articles_documents' => '#008000',
    'couleur_sous_regroupement_articles_documents' => '#0F056B',

    'utiliser_les_tarifs_forces_pour_les_nomenclatures' => false,
    'utiliser_les_tarifs_forces_pour_les_nomenclatures_enfant' => false,
    'utiliser_les_coefficients' => false,
    'calculateur_sur_document' => false,
    'saisie_documents_devise_etrangere' => false,
    'documents_devise_etrangere_champ_conversion' => 'tarif',
    'documents_devise_etrangere_griser_prix_converti' => true,

    'position_actions_saisie_des_articles' => 'les_deux',
    'saisie_documents_utiliser_les_titres' => true,
    'saisie_documents_utiliser_les_sauts_de_ligne' => true,
    'saisie_documents_utiliser_les_sauts_de_page' => true,
    'saisie_documents_utiliser_les_sous_totaux' => true,
    'saisie_documents_utiliser_les_commentaires' => true,
    'saisie_documents_utiliser_les_notes_internes' => true,
    'saisie_documents_utiliser_les_remises' => false,
    'saisie_documents_utiliser_les_images' => false,
    'utiliser_conditionnement' => false,
    'utiliser_reglage_marge_par_nature' => false,

    'saisie_documents_afficher_prix' => [
        'bl_vente' => false,
        'bl_achat' => false,
        'bon_preparation_vente' => false,
        'bon_retour_vente' => false,
    ],
    'gestion_integrite_sequence_chronologique' => false,
    'gestion_integrite_sequence_chronologique_par_annee' => false,

    'nombre_de_lignes_dans_listes' => 10,
    'nombre_de_lignes_historique_bouton' => 10,
    'tags_recherche_adresse_pour_client' => false,
    'tags_recherche_contact_pour_client' => false,
    'afficher_entrepot_sur_document_achat' => true,
    'nombre_de_tentatives_connexion_maximum_avant_blocage' => 10,
    'nombre_de_minutes_blocage' => 5,
    'adresse_mail_a_prevenir_robot' => 'support@eden-erp.fr',

    //Eco contribution
    'eco_contribution_document_fixant_valeur' => 'facture_vente',

    'gescom_document_onglet_projet_uniquement_projet_client' => true,

	// est ce qu'il doit y avoir un seul BR par commande achat ? (par jour)
    'gescom_un_seul_br_par_commande_achat_par_jour' => false,

	// Afficher les photos des articles sur les documents
    'gescom_photo_article_sur_document' => false,

	// Masquer des lignes sur les documents (ajoute une option sur chaque ligne pour les devis)
    'gescom_masquer_lignes_du_document' => false,

	// Calcul des stocks en live sur les devis
    'gescom_stocks_dispo_sur_devis' => false,

    // Calcul des stocks en live sur les commandes
    'gescom_stocks_dispo_sur_commande' => false,

	// Utiliser les variantes sur les devis
    'gescom_variantes_devis' => false,

    'mode_multi_adresses' => true,
    'mode_multi_entites' => false,
    'filtres_sur_liste' => false,
    'actualisation_automatique_sur_filtres_colonne_gauche' => false,

    'timeline_info_traitement' => false,

    'gescom_bloquer_suppression_client_si_presence_documents_commerciaux' => true,
    'gescom_bloquer_suppression_fournisseur_si_presence_documents_commerciaux' => true,

    'gescom_affacturage' => false,
    'gescom_ne_pas_facturer_bl_lors_fusion_vers_facture' => false,
    'gescom_ne_pas_facturer_commande_vente_lors_fusion_vers_facture' => false,
    'gescom_separation_dans_facturer_documents' => true,
    'gescom_separation_sous_total_dans_facturer_documents' => false,
    'gescom_activer_style_sur_ligne_document' => false,
    'gescom_ne_pas_generer_pdf_automatiquement' => false,

    'gescom_type_document_bloquer_pdf_post_validation' => $document_bloquer_pdf_post_validation,
    'gescom_visionnage_pdf_pre_validation' => true,

	// mode de saisie en gestion commerciale : onglet ou blocs à la suite
    'gescom_document_utiliser_onglets' => true,

    'gescom_document_bouton_rafraichir_article_tarifs' => false,

    'mise_a_jour_tarif_duplication' => 0,

    // pour les documents liés, on affiche les montants en ...
    'gescom_documents_lies_montant_en_ht_ou_ttc' => 'TTC',

	// à quel moment fait on l'arrondi pour calculer le total sur les documents ?
    'gescom_document_arrondi_par_ligne' => false,
    'gescom_regle_arrondi_ligne' => 'total',

	// afficher le récap des documents liés de manière détaillée
    'gescom_afficher_recap_documents_lies_avec_details' => true,
    'gescom_afficher_etat_reliquats_lignes_documents' => false,

	// ne pas afficher les blocs repliés sur la saisie des documents
    'gescom_ne_pas_plier_par_defaut_les_blocs_sur_saisie_document' => false,

	//Activer aide à la saisie des commandes achats depuis les documents de vente
	'aide_a_la_saisie_des_commandes_achats_depuis_les_documents_de_vente' =>true,

	// documents de gescom
    'gescom_devis_vente' => true,
    'gescom_commande_vente' => true,
    'gescom_bon_preparation_vente' => false,
    'gescom_bl_vente' => true,
    'gescom_bon_retour_vente' => false,
    'gescom_bon_retour_achat' => false,
    'gescom_acompte_vente' => true,
    'gescom_facture_vente' => true,
    'gescom_avoir_vente' => true,
    'gescom_devis_achat' => true,
    'gescom_commande_achat' => true,
    'gescom_bl_achat' => true,
    'gescom_acompte_achat' => true,
    'gescom_facture_achat' => true,
    'gescom_avoir_achat' => true,
    'gescom_document' => [

        'devis_vente' => true,
        'commande_vente' => true,
        'bon_preparation_vente' => false,
        'bl_vente' => true,
        'bon_retour_vente' => false,
        'bon_retour_achat' => false,
        'acompte_vente' => true,
        'facture_vente' => true,
        'avoir_vente' => true,
        'devis_achat' => true,
        'commande_achat' => true,
        'bl_achat' => true,
        'acompte_achat' => true,
        'facture_achat' => true,
        'avoir_achat' => true,
    ],
    'gescom_afficher_bandeau_du_bas' => true,

    'bandeau_bas_informations' => [
        'nombre_articles' => false,
        'total_quantite' => false,
        'total_ht' => true,
        'total_ttc' => true,
        'total_ttc_apres_remise' => true
    ],
    'gescom_gerer_avancement_via_projet' => true,
    'gescom_gerer_avancement_via_devis' => true,
    'gescom_afficher_titres_separations_facturation_avancement' => true,
    'gescom_avancement_accepter_reduction_avancement_actuel' => true,
    'gescom_avancement_pouvoir_modifier_pourcentage_precedent' => true,

    'gescom_creation_commande_automatique_validation_devis' => false,
    'gescom_document_remises_pied_de_page' => true,
    'gescom_document_coupon_reduction' => true,
    'gescom_document_cacher_totaux_sur_pdf' => false,
    'gescom_document_avec_nomenclature' => false,
    'gescom_bloquer_tarif_nomenclature' => false,
    'gescom_document_avancement' => false,
    'gescom_nomenclature_stocks_produits_finis' => true,
    'gescom_bloquer_suppression_article_avec_stock' => false,
    'gescom_eclatement_automatique_conditionnement_bl_achat' => false,
    'gescom_nomenclature_afficher_lignes' => true,
    'gescom_remplacement_article' => $document_affichage_option_replacement_article,

    'gescom_afficher_option_ajout_lot' => $document_affichage_option_ajout_lot,
	'commentaires_wysiwyg_documents' => false,
    'notes_internes_wysiwyg_documents' => false,
    'description_wysiwyg_documents' => false,

    'gescom_calcul_date_de_reglement_automatique' => true,
    'gescom_affichage_prix_achat_liste_articles' => false,
    'gescom_affichage_compteurs_articles' => false,

    'ecart_gestion_ttc' => $documents_ecart_gestion_ttc,
    'ecart_gestion_compte_produit' => null,
    'ecart_gestion_compte_charge' => null,
    'seuil_ecart_gestion_ttc' => 2,

	// affichage des totaux dans le recap de saisie d'un document
	'recap_saisie_document_afficher_total_ht' => true,
	'recap_saisie_document_afficher_total_tva' => true,
	'recap_saisie_document_afficher_remise_pourcentage' => true,
	'recap_saisie_document_afficher_remise_devises' => true,
	'recap_saisie_document_afficher_total_ttc' => true,

    // modules et fonctionnalités globales
    'gestion_de_projet' => true,

	// si on travaille en mode multi entité, mais qu'on ne souhaite avoir qu'une seule fiche client
    'fiche_client_unique_multi_entite' => false,

    // listes
    'listes_factures_autres_action_comptabiliser' => true,
    'listes_activer_menu_options_a_gauche' => false,

    'fiche_client_prospection' => true,
    'fiche_client_taches' => true,
    'fiche_client_onglet_par_defaut' => 'factures',

    'fiche_fournisseur_emails_recus' => false,
    'fiche_article_declinaisons' => false,
    'fiche_article_fournisseurs' => false,
    'fiche_article_historique_prix_d_achat_de_l_article' => false,
    'fiche_famille_articles_avec_photo' => false,
    'composition_des_articles' => false,
    'parametrage_avance_des_documents' => false,

	// boite de réception des factures fournisseur : ne pas traiter les mails qui ne contiennent pas de PDF
	'boite_reception_mails_fournisseurs_eden_pas_traiter_mails_sans_pdf' => true,

	// doit on bloquer la validation de documents si le client a un retard de paiement ?
    'bloquer_validation_devis_vente_si_client_retard_paiement' => false,
    'bloquer_validation_commande_vente_si_client_retard_paiement' => false,
    'bloquer_validation_bl_vente_si_client_retard_paiement' => false,
    'bloquer_validation_acompte_vente_si_client_retard_paiement' => false,
    'bloquer_validation_facture_vente_si_client_retard_paiement' => false,
    'bloquer_validation_avoir_vente_si_client_retard_paiement' => false,

	'bloquer_validation_devis_achat_si_client_retard_paiement' => false,
    'bloquer_validation_commande_achat_si_client_retard_paiement' => false,
    'bloquer_validation_document_si_client_retard_paiement' => [
        'devis_vente' => false,
        'commande_vente' => false,
        'bl_vente' => false,
        'acompte_vente' => false,
        'facture_vente' => false,
        'avoir_vente' => false,
        'devis_achat' => false,
        'commande_achat' => false,
    ],
    'frais_de_port_sur_documents_commerciaux' => false,

    'achats_sur_les_documents' => false,
    'gestion_credits' => false,
    'gescom_suppression_facture_valide' => 'creer_avoir',
    'annulation_acompte_par_avoir' => true,
    'utiliser_campagne_prospection' => true,

    'afficher_code_article_recherche' => true,

	// documents de gestion commerciale
    'modification_tva_impossible_sur_documents' => false,
    'alerte_tva_0_sur_document' => true,
    'relance_devis_vente_utilisateur' => false,
    'versionning_document' => $document_versionning_document,
    'choix_code_article_sur_saisie_document' => false,
    'affichage_code_article_nomenclature' => false,

    'afficher_encours_saisie_documents' => true,
    'type_remise_globale_en_montant' => 'TTC',

    'transformer_devis_vente_facture_pourcentage' => false,
    'transformer_fournisseurs_par_article_commande_achat_modale' => true,
    'transformer_regrouper_lignes_articles_commandes_vente_bl' => true,

    'alerte_document_prix_achat_nul' => true,

    // modifie le mode de calcul de la marge
    'type_calcul_du_pourcentage_marge' => 'prix_de_vente',

    'arrondi_sur_les_documents' => 2,

	// ajoute une colonne unités sur les documents commerciaux
    'ajout_colonne_unite_sur_documents' => false,
	// ajoute une colonne disponibilité sur les documents commerciaux
    'ajout_colonne_disponibilite_sur_documents' => [

        'devis_vente' => false,
        'commande_vente' => false,
        'bon_preparation_vente' => false,
        'bl_vente' => false,
        'bon_retour_vente' => false,
        'bon_retour_achat' => false,
        'acompte_vente' => false,
        'facture_vente' => false,
        'avoir_vente' => false,
        'devis_achat' => false,
        'commande_achat' => false,
        'bl_achat' => false,
        'acompte_achat' => false,
        'facture_achat' => false,
        'avoir_achat' => false,
    ],

	// doit on lier des contacts aux documents de gestion commerciale ?
    'lien_contacts_sur_documents' => true,

    // doit on afficher les paiements du client non rattache à un document
    'afficher_paiements_non_rattache_sur_documents' => true,

    // nombre de chiffres décimaux sur les tarifs
    'nombre_de_chiffres_decimaux_sur_les_tarif' => 2,

	// marge mini sur les documents commerciaux
	'marge_mini_sur_documents_commerciaux' => 0,
	'marge_recommandee_sur_documents_commerciaux' => 0,

	// nombre de chiffres pour la numérotation des documents
	'nombre_chiffres_numerotation' => 5,

    'calcul_tarif_sur_fiche_article_avec_marge' => false,

    'numero_de_la_ligne_de_relance_par_email' => false,

    'expediteur_en_copie_du_mail' => false,
    'email_utiliser_identifiants_comptes_emails' => false,

    'documents_adresses_pays' => false,


    //choix de la ligne qui se référe aux chèques dans la table mode de paiement

    'ligne_de_cheque_dans_mode_paiement' => 0,

	// calcul de la marge sur les projets
    'calcul_marges_type_element' => 'devis_vente',
    'calcul_marges_facture_achat' => true,
    'calcul_marges_achats_via_bl_vente' => false,
    'calcul_marges_achats_saisis_sur_documents' => true,

	// délai d'expiration des devis vente
	'delai_expiration_devis' => 30,

	// planning : combien de jours afficher par semaine ?
	'planning_nombre_jours' => 5,
	'planning_affichage_demi_journee' => true,
	'planning_affichage_demi_journee_debut_am' => '08:30',
	'planning_affichage_demi_journee_fin_am' => '12:30',
	'planning_affichage_demi_journee_debut_pm' => '14:30',
	'planning_affichage_demi_journee_fin_pm' => '17:30',
    'planning_afficher_avatar_utilisateur' => false,
    'planning_hauteur_ligne' => 1,
	/*
    // planning : 1 ou 2 semaines ?
	'planning_nombre_semaines' => 1,
	*/

	// synchro budget insight
	// par défaut la synchro bancaire est liée à une entité (au niveau du traitement des transactions)
	// on peut retirer ce principe pour les clients qui ont un seul compte en banque
	// pour plusieurs entités sur Eden
    'budget_insight_filtre_entite_pour_traitement_transactions' => true,
    'budget_insight_utiliser' => true,

	// nombre de caractères pour le calcul du compte auxiliaire en compta
    'compta_compte_auxiliaire_nombre_caracteres' => false,

    'id_mail_relance_moyen_de_paiement' => null,
	'id_mail_encaissement_cb' => null,

    'id_mode_de_paiement_cb_payline' => null,
    'id_mode_de_paiement_cb_stripe' => null,
    'id_mode_de_paiement_cb_payzen' => null,
    'id_mode_de_paiement_prelevement_payzen' => null,

    'compte_bancaire_cb_payline' => null,
    'compte_bancaire_cb_stripe' => null,
    'compte_bancaire_cb_payzen' => null,
    'compte_bancaire_prelevement_payzen' => null,

    'scribens_activation' => false,
    'scribens_api_key' => null,

	'numeros_de_serie' => false,

    'blocage_enregistrement_si_article_supprime' => ['devis_vente' => 0, 'commande_vente' => 0,  'bl_vente' => 0,  'bon_preparation_vente' => 0,  'bon_retour_vente' => 0,  'acompte_vente' => 0,  'facture_vente' => 0,  'avoir_vente' => 0, ],

    'type_document_numero_de_serie' => $documents_gescom_numero_de_serie,

    'type_document_generer_pdf' => $type_document_generer_pdf,

    'documents_colonnes_a_afficher_vente' => [
        'utilisateur_id' => false,
        'code_article' => false,
        'unite' => false,
        'prix_achat' => false,
        'marge_appliquee' => false,
        'tarif' => true,
        'remise' => true,
        'tarif_net' => false,
        'marge_brute_pourcentage' => false,
        'marge_brute_montant' => false,
        'marge_pourcentage' => false,
        'marge' => false,
        'total' => true,
        'eco_contribution' => false,
        'tva' => true,
        'categorie_comptable_article_id' => false,
        'total_ttc' => true,
    ],

    'documents_colonnes_a_afficher_achat' => [
        'utilisateur_id' => false,
        'code_article' => false,
        'unite' => false,
        'prix_achat' => false,
        'marge_appliquee' => false,
        'tarif' => true,
        'remise' => true,
        'tarif_net' => false,
        'marge_brute_pourcentage' => false,
        'marge_brute_montant' => false,
        'marge_pourcentage' => false,
        'marge' => false,
        'total' => true,
        'eco_contribution' => false,
        'tva' => true,
        'categorie_comptable_article_id' => false,
        'total_ttc' => true,
    ],

    'numeros_de_lot' => false,

	// si cette option est activée,
	// les utilisateurs doivent être spécifiquement autorisés à modifier
	// une facture de plus de 30 jours
	// c'est une colonne du même nom sur la table utilisateurs (0/1)
	// le nombre de jours est fixe pour le moment, et devra être mis en config par la suite
	// par défaut = 30 jours
	'autorise_a_modifier_factures_de_plus_de_x_jours' => false,

	// si ce champ est > à 0,
	// alors chaque mois à partir du X, il n'est plus possible d'éditer des documents du mois passé (ni avant)
	'cloture_comptable_mensuelle_le' => 0,

	// quelle est la date à prendre en compte sur les factures pour considérer qu'elle est due ?
	// généralement c'est soit la date du document (date), soit la date de règlement (date_de_reglement)
	'date_a_utiliser_pour_considerer_une_facture_comme_due' => 'date_de_reglement',

	// pour la synchro des mails, pour éviter d'indexer les mails internes
	'email_recus_ndd_interne' => '',

    'emails_actifs_intranet' => array('note_de_frais' => true, 'demande_conge' => true),

	// module maintenance
	'maintenance_delai_creation_intervention' => '2 months',

	// Onglet par défaut fiche client
	'onglet_par_defaut_fiche_client' => 'factures',

    // adresse email pour gestion des stocks
    'adresse_email_gestion_stocks' => '',

    'activer_gestion_stock' => false,

	// doit on activer les exports différés ?
    'limite_export_differe' => 50000,

    'calcul_stock_actuel_article_post_mouvement' => false,


    // Modèle de relance
    'modele_relance_1' => "",
    'modele_relance_2' => "",
    'modele_relance_3' => "",
    'modele_relance_4' => "",

    // Doit on créer une feuille de temps lorsqu'on crée une tache ?
    'creation_feuille_de_temps_pour_tache' => false,

    // Par défaut on active pas les notifications
    'notifications' => false,

	// paramétrage comptable
    'compta_article_id_pour_acompte' => '',
    'compta_journal_vente' => false,
    'compta_journal_achat' => false,
    'compta_journal_note_de_frais' => false,
    'compta_compte_general_clients' => false,
    'compta_compte_general_fournisseurs' => false,
    'compta_compte_general_salaries' => false,
    'compta_generation_paiement_via_avoirs' => false,
    'compta_banque_defaut_generation_paiement_via_avoir' => false,
    'compte_comptable_remise_vente' => false,
    'compte_comptable_remise_achat' => false,
    'compta_libelle_paiement' => '',
    'compta_libelle_note_de_frais' => '',

    // gestion de l'email qui envoie les mails d'oublie de mot de passe etc
    'adresse_mail_fonctionnement_application' => 'hello@exemple.com',
    'adresse_mail_support' => 'support@eden-erp.fr',
    'nom_mail_fonctionnement_application' => 'Exemple',

    // gestion de l'enregistrement des emails en échange
    'enregistrer_systematique_email_en_echange' => false,

    // gestion des entrepots sur les lignes
    'entrepot_sur_ligne' => true,

    // gestion des types de documents sur lequel l'ajout des pj des articles est utilisé
    'selection_pj_article_envoie_mail_documents_gescom' => $selection_pj_article_envoie_mail_documents_gescom,

	// nombre de décimales pour le helper montant
    'supprimer_les_zeros_superflux' => true,

    'dashboard_transmission_de_filtres' => false,

    //CRM
    'lier_questionnaires_satisfactions_projet' => false,
    'duree_des_taches' => '1;2;3;4;5',
    'choix_couleur_tache' => 'type_tache',
    'choix_couleur_tache_planning' => 'type_tache',
    'affichage_taches_journee_entiere' => 'bandeau',
    'modifier_tache_terminee' => true,
    'duree_max_creation_recurrence' => 12,
    'frequence_creation_occurrences_recurrence' => 1,
    'valeur_duree_ajoutee_recurrence_personnalisee' => 6,
    'unite_duree_ajoutee_recurrence_personnalisee' => 'month',
    'champs_non_repris_tache_parent' => [
        'terminee' => true,
    ],
    'utiliser_connexion_classique' => true,
    'utiliser_chronometre' => false,
    'intranet' => false,

	// crm : alimentation de la timeline
    'alimentation_timeline_creation_projet' => true,
    'alimentation_timeline_creation_ticket_client' => true,
    'alimentation_timeline_creation_tache' => 'les_2_taches',
    'alimentation_timeline_document_vente' => [
        'devis_vente' => true,
        'facture_vente' => false,
        'bl_vente' => false,
        'acompte_vente' => false,
        'avoir_vente' => false,
        'commande_vente' => false,
        'bon_retour_vente' => false,
        'bon_preparation_vente' => false,
    ],
    'alimentation_timeline_document_achat' => [
        'devis_achat' => false,
        'facture_achat' => false,
        'bl_achat' => false,
        'acompte_achat' => false,
        'avoir_achat' => false,
        'commande_achat' => false,
        'bon_retour_achat' => false,
    ],
    'alimentation_timeline_creation_devis_vente' => true,
    'alimentation_timeline_creation_commande_vente' => false,
    'alimentation_timeline_creation_bon_preparation_vente' => false,
    'alimentation_timeline_creation_bl_vente' => false,
    'alimentation_timeline_creation_acompte_vente' => false,
    'alimentation_timeline_creation_facture_vente' => false,
    'alimentation_timeline_creation_avoir_vente' => false,
    'alimentation_timeline_facture_vente' => false,
    'alimentation_timeline_avoir_vente' => false,
    'alimentation_timeline_acompte_vente' => false,

    // Intégration
    'microsoft_type_rdv_defaut' => '0',
    'microsoft_nombre_mois' => '3',
    'microsoft_rafraichissement_delta_token_avant_fin' => '30',
    'microsoft_app_id' => '',
    'microsoft_app_secret' => '',
    'microsoft_redirect_uri' => '',
    'microsoft_id_active_directory' => '',
    'sharepoint_utiliser_synchronisation' => false,
    'dossier_racine_eden_sharepoint' => false,
    'mfiles_utiliser_synchronisation' => false,
    'mfiles_url' => '',
    'mfiles_jeton_authentification' => '',
    'mfiles_attribut_eden_id' => '',
    'google_utiliser_connexion' => false,
    'google_app_id' => '',
    'google_app_secret' => '',
    'google_projet_id' => '',
    'google_redirect_uri' => '',
    'google_compte_service_id_client' => '',
    'google_compte_service_email' => '',
    'google_id_cle_privee' => '',
    'google_cle_privee' => '',
    'google_type_rdv_defaut' => 21,
    'google_nombre_mois' => 3,
    'cle_api_google' => '',
    'projet_id_ticket_eden' => '',
    'budget_insight_client_id' => config('services.budget_insight.client_id'),
    'budget_insight_client_secret' => config('services.budget_insight.client_secret'),
    'docusign_activation' => false,
    'docusign_client_id' => '',
    'docusign_rsa_public_key' => '',
    'docusign_rsa_private_key' => '',
    'docusign_utilisateur_id' => '',
    'stripe_public_key' => '',
    'stripe_private_key' => '',
    'stripe_public_key_test' => '',
    'stripe_private_key_test' => '',
    'stripe_live' => false,
    'stripe_activation' => true,
    'microsoft_utiliser_connexion' => false,
    'mindee_api_key' => '',

    'releve_mail_jour_delai' => 7,

    'releve_mail_ticket_client_microsoft' => false,
    'releve_mail_microsoft_nombre_a_recuperer' => 10,
    'releve_mail_ticket_client_microsoft_email' => '',
    'releve_mail_ticket_client_microsoft_dossier' => 0,
    'releve_mail_ticket_client_microsoft_dossier_deplacement_apres_traitement' => 0,
    'releve_mail_ticket_client_modele_email_creation_ticket' => '',
    'releve_mail_ticket_client_modele_email_reponse_ticket' => '',
    'releve_mail_ticket_client_modele_email_reponse_client_mail_ticket' => '',
    'releve_mail_ticket_client_modele_email_cloture_ticket' => '',
    'releve_mail_ticket_client_reconnaitre_client_via_domaine' => false,
    'releve_mail_ticket_client_extension_piece_jointe_bloque' => 'bat;exe',
    'releve_mail_ticket_client_taille_maximale_piece_jointe_en_mo' => '30',


    'payline_activation' => false,
    'payzen_activation' => false,

    'mois_bloquant_saisie_note_de_frais' => '0',

    'recaptcha_cle_public' => '',
    'recaptcha_cle_prive' => '',
    
    'accessibilite' => false,

    'open_ai_activation' => false,
    'open_ai_cle' => '',
    'open_ai_project_id' => '',
    
    'renouvellement_mot_de_passe_utilisateur' => false,
    'duree_validite_mot_de_passe_utilisateur' => 90,

    'renouvellement_mot_de_passe_utilisateur_extranet' => false,
    'duree_validite_mot_de_passe_utilisateur_extranet' => 90
];

$definitive = array();

foreach($standard as $index => $valeur_standard) {

    $definitive[$index] = $valeur_standard;
}

foreach($specifique as $index => $valeur_specifique) {

    if (isset($definitive[$index]) && is_array($definitive[$index]) && is_array($valeur_specifique))
        $valeur_specifique = array_merge($definitive[$index], $valeur_specifique);

    $definitive[$index] = $valeur_specifique;
}


if($script_regroupement_fonctionnalite_ok) {

    foreach ($definitive['gescom_document'] as $type_element => $valeur) {

        $definitive['gescom_' . $type_element] = $valeur;
    }
}

// On décode les champs de type mot de passe pour l'affichage et les comparaisons
$management_test = new \App\Eden\Managements\Fonctionnalites\Fonctionnalite_management;

$fonctionnalites_password = $management_test->toutes_fonctionnalites_par_type('password');

foreach ($definitive as $nom => $valeur){

    if(in_array($nom, $fonctionnalites_password))
        $definitive[$nom] = base64_decode($valeur);
}

return $definitive;
