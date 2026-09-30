<?php
 return array (
  'table_libre' =>
  array (
    'nom_table' => 'Sociétés',
    'nom_table_sql' => 'client',
    'element' => 'société',
    'type_element' => 'client',
    'element_pluriel' => 'sociétés',
    'fiche' => 1,
    'disponible_recherche_rapide' => 1,
    'creation_rapide' => 1,
    'affichage_recherche' => '#raison_sociale# ',
    'affichage_fiche_type' => '#raison_sociale# ',
    'affichage_dans_liste' => '#raison_sociale#, #prenom#',
    'affichage_pour_select' => '#raison_sociale# ',
    'editable_client' => 1,
    'categorie' => 'element_primaire',
    'icone_fontawesome' => 'fa-users',
  ),
  'champs_libres' =>
  array (
    'civilite' =>
    array (
      'type_element' => 'client',
      'nom' => 'civilite',
      'nom_sql' => 'civilite',
      'type' => 20,
      'liste_choix' => 39,
    ),
    'raison_sociale' =>
    array (
      'type_element' => 'client',
      'nom' => 'Raison sociale',
      'format_champ' => 'majuscule',
      'nom_sql' => 'raison_sociale',
      'recherche' => 1,
    ),
    'nom' =>
    array (
      'type_element' => 'client',
      'nom' => 'Nom',
      'nom_sql' => 'nom',
      'recherche' => 1,
    ),
    'prenom' =>
    array (
      'type_element' => 'client',
      'nom' => 'Prénom',
      'nom_sql' => 'prenom',
      'recherche' => 1,
    ),
    'mot_de_passe' =>
    array (
      'type_element' => 'client',
      'nom' => 'Mot de passe',
      'nom_sql' => 'mot_de_passe',
    ),
    'adresse' =>
    array (
      'type_element' => 'client',
      'nom' => 'Adresse',
      'nom_sql' => 'adresse',
    ),
    'adresse_complement' =>
    array (
      'type_element' => 'client',
      'nom' => 'Complément',
      'nom_sql' => 'adresse_complement',
    ),
    'code_postal' =>
    array (
      'type_element' => 'client',
      'nom' => 'Code postal',
      'nom_sql' => 'code_postal',
      'format_champ' => 'code_postal',
      'contenu' => '{"region":"region","ville":"ville"}'
    ),
    'ville' =>
    array (
      'type_element' => 'client',
      'nom' => 'Ville',
      'nom_sql' => 'ville',
    ),
    'region' =>
    array(
        'type_element' => 'client',
        'nom' => 'Région',
        'nom_sql' => 'region',
    ),
    'telephone' =>
    array (
      'type_element' => 'client',
      'nom' => 'Téléphone',
      'format_champ' => 'numero_telephone',
      'nom_sql' => 'telephone',
    ),
    'adresse_email' =>
    array (
      'type_element' => 'client',
      'nom' => 'Adresse email',
      'format_champ' => 'email',
      'nom_sql' => 'adresse_email',
    ),
    'pays' =>
    array (
      'type_element' => 'client',
      'nom' => 'Pays',
      'nom_sql' => 'pays',
      'type' => 1,
    ),
    'entite_id' =>
    array (
      'type_element' => 'client',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'groupe_recouvrement_id' =>
    array (
      'type_element' => 'client',
      'nom' => 'Groupe de recouvrement',
      'nom_sql' => 'groupe_recouvrement_id',
      'type' => 20,
      'liste_choix' => 40,
    ),
    'portefeuille_payline' =>
    array (
      'type_element' => 'client',
      'nom' => 'Portefeuille (payline)',
      'nom_sql' => 'portefeuille_payline',
    ),
    'lien_mise_a_jour_cb_stripe' =>
    array (
      'type_element' => 'client',
      'nom' => 'Lien maj CB (stripe)',
      'nom_sql' => 'lien_mise_a_jour_cb_stripe',
      'lecture_seule' => 1,
    ),
    'portefeuille_payzen_cb' =>
    array (
      'type_element' => 'client',
      'nom' => 'Token CB Payzen',
      'nom_sql' => 'portefeuille_payzen_cb',
    ),
    'portefeuille_payzen_prelevement' =>
    array (
      'type_element' => 'client',
      'nom' => 'Token IBAN Payzen',
      'nom_sql' => 'portefeuille_payzen_prelevement',
    ),
    'lien_mise_a_jour_payzen' =>
    array (
      'type_element' => 'client',
      'nom' => 'Lien maj (Payzen)',
      'nom_sql' => 'lien_mise_a_jour_payzen',
      'lecture_seule' => 1,
    ),
    'portefeuille_stripe' =>
    array (
      'type_element' => 'client',
      'nom' => 'Portefeuille (stripe)',
      'nom_sql' => 'portefeuille_stripe',
    ),
    'potentiel' =>
    array (
      'type_element' => 'client',
      'nom' => 'Potentiel',
      'nom_sql' => 'potentiel',
      'type' => 2,
    ),
    'etape_de_prospection' =>
    array (
      'type_element' => 'client',
      'nom' => 'Etape de prospection',
      'nom_sql' => 'etape_de_prospection',
      'type' => 1,
    ),
    'interet' =>
    array (
      'type_element' => 'client',
      'nom' => 'Intérêt',
      'nom_sql' => 'interet',
      'type' => 20,
      'liste_choix' => 45,
    ),
    'prestataire_actuel' =>
    array (
      'type_element' => 'client',
      'nom' => 'Prestataire actuel',
      'nom_sql' => 'prestataire_actuel',
    ),
    'forcer_tva_0' =>
    array (
      'type_element' => 'client',
      'nom' => 'Forcer la TVA à 0',
      'nom_sql' => 'forcer_tva_0',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'modalite_paiement_id' =>
    array (
      'type_element' => 'client',
      'nom' => 'Conditions de paiement',
      'nom_sql' => 'modalite_paiement_id',
      'type' => 20,
      'liste_choix' => 6,
    ),
    'compte_bancaire_id' =>
    array (
      'type_element' => 'client',
      'nom' => 'Compte bancaire',
      'nom_sql' => 'compte_bancaire_id',
      'type' => 20,
      'liste_choix' => 4,
    ),
    'stripe_payment_method' =>
    array (
      'type_element' => 'client',
      'nom' => 'Stripe payment methods',
      'nom_sql' => 'stripe_payment_method',
    ),
    'lien_mise_a_jour_cb_payline' =>
    array (
      'type_element' => 'client',
      'nom' => 'Lien mise à jour CB Payline',
      'nom_sql' => 'lien_mise_a_jour_cb_payline',
    ),
    'mode_paiement_id' =>
    array (
      'type_element' => 'client',
      'nom' => 'Mode de paiement',
      'nom_sql' => 'mode_paiement_id',
      'type' => 20,
      'liste_choix' => 7,
    ),
    'forcer_compte_comptable' =>
    array (
      'type_element' => 'client',
      'nom' => 'Forcer le compte comptable',
      'format_champ' => 'select',
      'nom_sql' => 'forcer_compte_comptable',
      'type' => 42,
      'type_element_ajax' => 'compte_comptable',
    ),
    'compte_auxiliaire' =>
    array (
      'type_element' => 'client',
      'nom' => 'Compte auxiliaire',
      'nom_sql' => 'compte_auxiliaire',
    ),
    'archive' =>
    array (
      'type_element' => 'client',
      'nom' => 'Archivé',
      'nom_sql' => 'archive',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'demander_info_paiement' =>
    array (
      'type_element' => 'client',
      'nom' => 'Demander les informations de paiement',
      'nom_sql' => 'demander_info_paiement',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'ca' =>
    array (
      'type_element' => 'client',
      'nom' => 'CA',
      'nom_sql' => 'ca',
      'type' => 3,
    ),
    'ca_12_mois' =>
    array (
      'type_element' => 'client',
      'nom' => 'CA 12 mois glissants',
      'nom_sql' => 'ca_12_mois',
      'type' => 3,
    ),
    'ca_depuis_janvier' =>
    array (
      'type_element' => 'client',
      'nom' => 'CA depuis janvier',
      'nom_sql' => 'ca_depuis_janvier',
      'type' => 3,
    ),
    'encours' =>
    array (
      'type_element' => 'client',
      'nom' => 'Encours',
      'nom_sql' => 'encours',
      'type' => 3,
    ),
    'date_de_derniere_facture' =>
    array (
      'type_element' => 'client',
      'nom' => 'Dernière facture',
      'nom_sql' => 'date_de_derniere_facture',
      'type' => 4,
    ),
    'date_de_dernier_echange' =>
    array (
      'type_element' => 'client',
      'nom' => 'Dernier échange',
      'nom_sql' => 'date_de_dernier_echange',
      'type' => 4,
    ),
    'numero_de_tva' =>
    array (
      'type_element' => 'client',
      'nom' => 'numéro de TVA',
      'nom_sql' => 'numero_de_tva',
    ),
    'refuse_facturation_groupee' =>
    array (
      'type_element' => 'client',
      'nom' => 'Refuse la facturation groupée',
      'nom_sql' => 'refuse_facturation_groupee',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'modele_document_defaut_devis_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. devis',
      'nom_sql' => 'modele_document_defaut_devis_vente',
      'type' => 20,
      'liste_choix' => 83,
    ),
    'modele_document_defaut_commande_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. commande',
      'nom_sql' => 'modele_document_defaut_commande_vente',
      'type' => 20,
      'liste_choix' => 93,
    ),
    'modele_document_defaut_bl_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. BL',
      'nom_sql' => 'modele_document_defaut_bl_vente',
      'type' => 20,
      'liste_choix' => 86,
    ),
    'modele_document_defaut_acompte_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. acompte',
      'nom_sql' => 'modele_document_defaut_acompte_vente',
      'type' => 20,
      'liste_choix' => 84,
    ),
    'modele_document_defaut_facture_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. facture',
      'nom_sql' => 'modele_document_defaut_facture_vente',
      'type' => 20,
      'liste_choix' => 87,
    ),
    'modele_document_defaut_avoir_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. avoir',
      'nom_sql' => 'modele_document_defaut_avoir_vente',
      'type' => 20,
      'liste_choix' => 85,
    ),
    'modele_document_defaut_bon_preparation_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. bon de préparation',
      'nom_sql' => 'modele_document_defaut_bon_preparation_vente',
      'type' => 20,
      'liste_choix' => 191,
    ),
    'modele_document_defaut_bon_retour_vente' =>
    array (
      'type_element' => 'client',
      'nom' => 'Modèle de doc. bon de retour',
      'nom_sql' => 'modele_document_defaut_bon_retour_vente',
      'type' => 20,
      'liste_choix' => 192,
    ),
    'encours_max_autorise' =>
    array (
      'type_element' => 'client',
      'nom' => 'Encours maximum autorisé',
      'nom_sql' => 'encours_max_autorise',
      'type' => 2,
    ),
    'responsable_commercial' =>
    array (
      'type_element' => 'client',
      'nom' => 'Responsable commercial',
      'nom_sql' => 'responsable_commercial',
      'type' => 42,
      'type_element_ajax' => 'utilisateur',
    ),
    'date_derniere_synchro_send_in_blue' =>
    array (
      'type_element' => 'client',
      'nom' => 'Date de dernière synchro Send In Blue',
      'nom_sql' => 'date_derniere_synchro_send_in_blue',
      'type' => 5,
    ),
    'categorie_comptable_id' =>
    array (
      'type_element' => 'client',
      'nom' => 'Catégorie comptable',
      'nom_sql' => 'categorie_comptable_id',
      'type' => 42,
      'type_element_ajax' => 'categorie_comptable',
    ),
    'logo' =>
    array (
      'type_element' => 'client',
      'nom' => 'Logo',
      'format_champ' => 'logo',
      'nom_sql' => 'logo',
      'type' => 7,
      'type_fichier' => 'logo',
    ),
    'linkedin' =>
    array (
      'type_element' => 'client',
      'nom' => 'Linkedin',
      'format_champ' => 'url',
      'nom_sql' => 'linkedin',
      'contenu' => '["fab fa-linkedin-in","#ffffff","#3c5898"]',
    ),
    'facebook' =>
    array (
      'type_element' => 'client',
      'nom' => 'Facebook',
      'format_champ' => 'url',
      'nom_sql' => 'facebook',
      'contenu' => '["fab fa-facebook-f","#ffffff","#3c5898"]',
    ),
    'maps' =>
    array (
      'type_element' => 'client',
      'nom' => 'Maps',
      'format_champ' => 'url',
      'nom_sql' => 'maps',
      'contenu' => '["fas fa-map-marker-alt","#ffffff","#f75d50"]',
    ),
    'societe_com' =>
    array (
      'type_element' => 'client',
      'nom' => 'Societe.com',
      'format_champ' => 'url',
      'nom_sql' => 'societe_com',
      'contenu' => '["fas fa-info","#ffffff","#008de4"]',
    ),
    'twitter' =>
    array (
      'type_element' => 'client',
      'nom' => 'Twitter',
      'format_champ' => 'url',
      'nom_sql' => 'twitter',
      'contenu' => '["fab fa-twitter","#ffffff","#1ca2f1"]',
    ),
    'recur' =>
    array (
      'type_element' => 'client',
      'nom' => 'Recur (paramétrage SEPA)',
      'nom_sql' => 'recur',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'date_signature_du_mandat' =>
    array (
      'type_element' => 'client',
      'nom' => 'Date signature du mandat (paramétrage SEPA)',
      'nom_sql' => 'date_signature_du_mandat',
      'type' => 4,
    ),
    'numero_du_mandat' =>
    array (
      'type_element' => 'client',
      'nom' => 'Numéro du mandat (paramétrage SEPA)',
      'nom_sql' => 'numero_du_mandat',
    ),
    'bic' =>
    array (
      'type_element' => 'client',
      'nom' => 'BIC (paramétrage SEPA)',
      'nom_sql' => 'bic',
    ),
    'iban' =>
    array (
      'type_element' => 'client',
      'nom' => 'IBAN (paramétrage SEPA)',
      'nom_sql' => 'iban',
    ),
    'denomination' =>
    array (
      'type_element' => 'client',
      'nom' => 'Dénomiation (paramétrage SEPA)',
      'nom_sql' => 'denomination',
    ),
    'code_client' =>
    array (
      'type_element' => 'client',
      'nom' => 'Code client (paramétrage SEPA)',
      'nom_sql' => 'code_client',
    ),
    'domaine_email' =>
    array (
      'type_element' => 'client',
      'nom' => 'Domaine email',
      'nom_sql' => 'domaine_email',
    ),
    'siret' =>
    array (
      'type_element' => 'client',
      'nom' => 'Siret',
      'format_champ' => 'siret',
      'nom_sql' => 'siret',
    ),
    'siren' =>
    array (
      'type_element' => 'client',
      'nom' => 'Siren',
      'format_champ' => 'siren',
      'nom_sql' => 'siren',
    ),
    'tiers_payeurs' =>
    array (
      'type_element' => 'client',
      'nom' => 'Tiers payeurs',
      'nom_sql' => 'tiers_payeurs',
      'type' => 10,
      'type_element_ajax' => 'client',
      'table_pivot' => 'client_tiers_payeurs',
    ),
    'eco_contribution' =>
    array (
      'type_element' => 'client',
      'nom' => 'Appliquer l\'éco-contribution',
      'nom_sql' => 'eco_contribution',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'type' =>
    array (
      'type_element' => 'client',
      'nom' => 'Type',
      'nom_sql' => 'type',
      'type' => 1,
      'valeur_defaut' => '3',
      'cacher_sans_valeur' => 1,
    ),
    'statut' =>
    array (
      'type_element' => 'client',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 1,
      'valeur_defaut' => '1',
      'cacher_sans_valeur' => 1,
    ),
    'site_web' =>
    array (
      'type_element' => 'client',
      'nom' => 'Site web',
      'format_champ' => 'url',
      'nom_sql' => 'site_web',
      'contenu' => '["fas fa-link","#ffffff","#5b40ff"]',
    ),
    'waze' =>
    array (
      'type_element' => 'client',
      'nom' => 'Waze',
      'format_champ' => 'url',
      'nom_sql' => 'waze',
      'contenu' => '["fas fa-car","#ffffff","#6fcdec"]',
    ),
    'pappers' =>
    array (
      'type_element' => 'client',
      'nom' => 'Pappers',
      'format_champ' => 'url',
      'nom_sql' => 'pappers',
      'contenu' => '["far fa-file-alt","#ffffff","#5583b4"]',
    ),
    'catalogue_groupement_id' => [
        'nom' => "Catalogue groupement",
        'type' => 42,
        'type_element_ajax' => 'catalogue_groupement',
        'filtres' => [
					'catalogue_groupement' => array (
						array (
							'operateur' => 0,
							'exclu' => 0,
							'blocs' => array (),
							'filtres' => 
							array (
								array (
									'type_element' => 'catalogue_groupement',
									'champ_liaison' => NULL,
									'valeurs' => 'lien_champ|client.entite_id',
									'nom_sql' => 'entite_id',
									'operateur' => 0,
								),
								array (
									'type_element' => 'catalogue_groupement',
									'champ_liaison' => NULL,
									'valeurs' => array(0),
									'nom_sql' => 'archive',
									'operateur' => 0,
								),
							),
						),
					),
				],
    ],
    'annuaire_facturation_id' => [
        'nom' => "Annuaire de facturation par défaut",
        'nom_sql' => "annuaire_facturation_id",
        'type' => 42,
        'type_element_ajax' => 'annuaire_facturation',
        'filtres' => [
            'annuaire_facturation' => array(
                array(
                    'operateur' => 0,
                    'exclu' => 0,
                    'blocs' => array(),
                    'filtres' => array(
                        array(
                            'type_element' => 'annuaire_facturation',
                            'champ_liaison' => NULL,
                            'valeurs' => 'lien_champ|client.id',
                            'nom_sql' => 'client_id',
                            'operateur' => 0,
                        ),
                        array (
                          'type_element' => 'annuaire_facturation',
                          'champ_liaison' => NULL,
                          'valeurs' => [1],
                          'nom_sql' => 'active',
                          'operateur' => 0,
                        ),
                    ),
                ),
            ),
        ],
    ],
  ),
);
