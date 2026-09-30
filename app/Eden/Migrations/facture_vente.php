<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Factures',
    'nom_table_sql' => 'facture_vente',
    'feminin' => 'e',
    'element' => 'facture',
    'type_element' => 'facture_vente',
    'element_pluriel' => 'factures',
    'disponible_recherche_rapide' => 1,
    'creation_rapide' => 1,
    'module' => 'Gestion commerciale',
    'affichage_recherche' => '<b>#reference_document#</b> - #date#  -  <b>#client_id#</b>',
    'affichage_dans_liste' => '#reference_document#',
    'affichage_dans_kanban' => '<b>#reference_document#</b> - #date#  -  <b>#client_id#</b>',
    'editable_client' => 1,
    'categorie' => 'documents_de_ventes',
    'icone_fontawesome' => 'fa-shopping-cart',
    'envoyer_email' => 1,
  ),
  'champs_libres' => 
  array (
    'date' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
    ),
    'date_changement_statut' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Réglée le',
      'nom_sql' => 'date_changement_statut',
      'type' => 4,
    ),
    'modalite_paiement_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Modalité de paiement',
      'nom_sql' => 'modalite_paiement_id',
      'type' => 20,
      'liste_choix' => 6,
    ),
    'mode_paiement_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Mode de paiement',
      'nom_sql' => 'mode_paiement_id',
      'type' => 20,
      'liste_choix' => 7,
    ),
    'compte_bancaire_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Banque',
      'nom_sql' => 'compte_bancaire_id',
      'type' => 20,
      'liste_choix' => 4,
    ),
    'projet_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'projet',
      'index' => 1,
    ),
    'nature_article_modele' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Modèle de marges par nature',
      'nom_sql' => 'nature_article_modele',
      'type' => 42,
      'type_element_ajax' => 'nature_article_modele',
    ),
    'commentaires' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Commentaires',
      'nom_sql' => 'commentaires',
      'type' => 6,
    ),
    'reference_document' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Référence',
      'nom_sql' => 'reference_document',
      'recherche' => 1,
    ),
    'date_de_reglement' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Date règlement',
      'nom_sql' => 'date_de_reglement',
      'type' => 4,
    ),
    'objet' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Objet',
      'nom_sql' => 'objet',
    ),
    'entite_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'client_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'client',
      'index' => 1,
    ),
    'client_id_tiers_payeur' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Tiers payeur',
      'format_champ' => 'select',
      'nom_sql' => 'client_id_tiers_payeur',
      'type' => 42,
      'type_element_ajax' => 'client',
      'filtres' => [
          'client' => array (
              array (
                  'operateur' => 0,
                  'exclu' => 0,
                  'blocs' => array (),
                  'filtres' => 
                  array (
                      array (
                          'type_element' => 'client',
                          'champ_liaison' => NULL,
                          'valeurs' => 'lien_champ|facture_vente.client_id',
                          'nom_sql' => 'tiers_payeurs',
                          'operateur' => 0,
                      ),
                  ),
              ),
          ), 
      ],
    ),
    'montant_document_ht' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'HT',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ht',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'montant_document_ttc' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'TTC',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ttc',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'montant_document_tva' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'TVA',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_tva',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'solde_document_ttc' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Solde TTC',
      'nom_sql' => 'solde_document_ttc',
      'type' => 3,
    ),
    'frais_de_livraison' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Frais de livraison',
      'nom_sql' => 'frais_de_livraison',
      'type' => 3,
    ),
    'comptabilise' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Comptabilisée',
      'nom_sql' => 'comptabilise',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'valide' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Validée',
      'nom_sql' => 'valide',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'valide_n1' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Validée par le N-1',
      'nom_sql' => 'valide_n1',
      'type' => 20,
      'liste_choix' => 3,
    ),
    'regle' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Réglée',
      'nom_sql' => 'regle',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'canal' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Canal',
      'nom_sql' => 'canal',
      'type' => 20,
      'liste_choix' => 32,
      'modification_post_validation' => 1,
    ),
    'client_livraison_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Client livré',
      'nom_sql' => 'client_livraison_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'adresse_de_livraison' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'filtres' => [
        'adresse' => array (
          array (
            'operateur' => 0,
            'exclu' => 0,
            'blocs' => array (),
            'filtres' => 
            array (
              array (
                'type_element' => 'adresse',
                'champ_liaison' => NULL,
                'valeurs' => 'lien_champ|facture_vente.client_id',
                'nom_sql' => 'client_id',
                'operateur' => 0,
              ),
              array (
                'type_element' => 'adresse',
                'champ_liaison' => NULL,
                'valeurs' => array (2,3),
                'nom_sql' => 'type_adresse',
                'operateur' => 0,
              ),
            ),
          ),
        ), 
			],
    ),
    'adresse_de_facturation' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Adresse de facturation',
      'nom_sql' => 'adresse_de_facturation',
      'type' => 42,
      'type_element_ajax' => 'adresse',
      'filtres' => [
        'adresse' => array (
          array (
            'operateur' => 0,
            'exclu' => 0,
            'blocs' => array (),
            'filtres' => 
            array (
              array (
                'type_element' => 'adresse',
                'champ_liaison' => NULL,
                'valeurs' => 'lien_champ|facture_vente.client_id',
                'nom_sql' => 'client_id',
                'operateur' => 0,
              ),
              array (
                'type_element' => 'adresse',
                'champ_liaison' => NULL,
                'valeurs' => array (1,3),
                'nom_sql' => 'type_adresse',
                'operateur' => 0,
              ),
            ),
          ),
        ), 
			],
    ),
    'remise_globale' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Remise globale',
      'nom_sql' => 'remise_globale',
      'type' => 3,
    ),
    'remise_globale_type' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Remise globale (type)',
      'nom_sql' => 'remise_globale_type',
      'type' => 2,
    ),
    'relance_1' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 1',
      'nom_sql' => 'relance_1',
      'type' => 4,
      'modification_post_validation' => 1,
    ),
    'relance_2' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 2',
      'nom_sql' => 'relance_2',
      'type' => 4,
      'modification_post_validation' => 1,
    ),
    'relance_3' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 3',
      'nom_sql' => 'relance_3',
      'type' => 4,
      'modification_post_validation' => 1,
    ),
    'relance_4' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 4',
      'nom_sql' => 'relance_4',
      'type' => 4,
      'modification_post_validation' => 1,
    ),
    'relance_1_ok' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 1 OK',
      'nom_sql' => 'relance_1_ok',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'relance_2_ok' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 2 OK',
      'nom_sql' => 'relance_2_ok',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'relance_3_ok' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 3 OK',
      'nom_sql' => 'relance_3_ok',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'relance_4_ok' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Relance 4 OK',
      'nom_sql' => 'relance_4_ok',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'commentaires_recouvrement' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Commentaires recouvrement',
      'nom_sql' => 'commentaires_recouvrement',
      'type' => 6,
      'modification_post_validation' => 1,
    ),
    'responsable_commercial_id' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Responsable commercial',
      'nom_sql' => 'responsable_commercial_id',
      'type' => 42,
      'modification_post_validation' => 1,
      'valeur_defaut' => '#utilisateur_connecte#',
      'type_element_ajax' => 'utilisateur',
      'desactiver_creation_a_la_volee' => 1,
    ),
    'annulee_par_avoir' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Annulée par avoir',
      'nom_sql' => 'annulee_par_avoir',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'avoir_partiel' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Avoir partiel',
      'nom_sql' => 'avoir_partiel',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'avoir_total' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Avoir total',
      'nom_sql' => 'avoir_total',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'annule' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Annulée',
      'nom_sql' => 'annule',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'lien_interface_paiement_payline' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Lien interface paiement Payline',
      'nom_sql' => 'lien_interface_paiement_payline',
    ),
    'lien_interface_paiement_stripe' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Lien interface paiement Stripe',
      'nom_sql' => 'lien_interface_paiement_stripe',
    ),
    'date_de_reglement_reelle' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Date de réglement réelle',
      'nom_sql' => 'date_de_reglement_reelle',
      'type' => 4,
      'modification_post_validation' => 1,
    ),
    'delai_de_reglement' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Délai de réglement',
      'nom_sql' => 'delai_de_reglement',
      'type' => 2,
      'modification_post_validation' => 1,
    ),
    'cacher_totaux_sur_pdf' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Cacher les totaux dans le document PDF',
      'nom_sql' => 'cacher_totaux_sur_pdf',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'pdf' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'PDF',
      'nom_sql' => 'pdf',
    ),
    'coupon_reduction' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Coupon réduction',
      'nom_sql' => 'coupon_reduction',
      'type' => 1,
    ),
    'annulee_v1' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Annulée v1',
      'nom_sql' => 'annulee_v1',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'adresse_de_livraison_texte' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison_texte',
      'type' => 6,
    ),
    'adresse_de_facturation_texte' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_facturation_texte',
      'type' => 6,
    ),
    'commentaire_fiche' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Commentaires internes',
      'nom_sql' => 'commentaire_fiche',
      'type' => 6,
    ),
    'piece_jointe' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Pièce jointe',
      'nom_sql' => 'piece_jointe',
      'type' => 7,
    ),
    'montant_reduction_coupon_reduction' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Montant réduction coupon réduction',
      'nom_sql' => 'montant_reduction_coupon_reduction',
      'type' => 3,
    ),
    'type_facture' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Type de facture',
      'nom_sql' => 'type_facture',
      'type' => 20,
      'liste_choix' => 75,
    ),
    'document_relu' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Document relu',
      'nom_sql' => 'document_relu',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'document_relu_par' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Document relu par',
      'nom_sql' => 'document_relu_par',
      'type' => 42,
      'type_element_ajax' => 'utilisateur',
    ),
    'document_relu_le' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Document relu le',
      'nom_sql' => 'document_relu_le',
      'type' => 5,
    ),
    'contacts_ids' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Contacts',
      'nom_sql' => 'contacts_ids',
      'type' => 10,
      'type_element_ajax' => 'contact',
      'table_pivot' => 'facture_vente_contacts_ids',
      'filtres' => [
          'contact' => array (
              array (
                  'operateur' => 0,
                  'exclu' => 0,
                  'blocs' => array (),
                  'filtres' => 
                  array (
                      array (
                          'type_element' => 'contact',
                          'champ_liaison' => NULL,
                          'valeurs' => 'lien_champ|facture_vente.client_id',
                          'nom_sql' => 'client_id',
                          'operateur' => 0,
                      ),
                  ),
              ),
          ), 
      ],
    ),
    'type_modele_document' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Modèle de document',
      'nom_sql' => 'type_modele_document',
      'type' => 20,
      'liste_choix' => 87,
      'modification_post_validation' => 1,
    ),
    'numero_commande_client' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Référence commande client',
      'nom_sql' => 'numero_commande_client',
    ),
    'frais_de_port_saisie' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Frais de port saisie',
      'nom_sql' => 'frais_de_port_saisie',
      'type' => 3,
    ),
    'statut' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 104,
    ),
    'marge' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Marge',
      'nom_sql' => 'marge',
      'type' => 3,
    ),
    'envoye_par_mail' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Envoyée par mail',
      'nom_sql' => 'envoye_par_mail',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
      'catalogue_groupement_id' => [
          'nom' => 'Catalogue de conditions commerciales',
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
                    'valeurs' => array (0),
                    'nom_sql' => 'archive',
                    'operateur' => 0,
                  ),
                ),
              ),
            ), 
          ],
          'correspondance_fiche_tiers' => 'catalogue_groupement_id'
      ],
    'retard' =>
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Retard',
      'nom_sql' => 'retard',
      'type' => 2,
    ),
    'marge_par_nature' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Marge par nature',
      'nom_sql' => 'marge_par_nature',
      'type' => 6,
    ),
    'devise' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Devise',
      'nom_sql' => 'devise',
      'type' => 20,
      'liste_choix' => 25,
    ),
    'taux_de_change' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Taux de change',
      'nom_sql' => 'taux_de_change',
      'type' => 3,
    ),
    'id_recurrence' => 
    array (
      'type_element' => 'facture_vente',
      'nom' => 'Recurrence',
      'nom_sql' => 'id_recurrence',
      'type' => 42,
      'type_element_ajax' => 'eden_recurrence_elements',
    ),
    'categorie_comptable_id' => [
        'nom' => 'Catégorie comptable',
        'type' => 42,
        'type_element_ajax' => 'categorie_comptable'
    ],
    'ecart_gestion_ttc' => [
				'nom' => "Écart de gestion TTC",
				'type' => 3,
			],
    'statut_facturation_electronique' => [
        'nom' => 'Statut facturation électronique',
        'type' => 20,
        'liste_choix' => 729,
        'modification_post_validation' => 1
    ],
    'annuaire_facturation_id' => [
        'nom' => 'Annuaire de facturation',
        'type' => 42,
        'type_element_ajax' => 'annuaire_facturation',
        'modification_post_validation' => 1,
        'correspondance_fiche_tiers' => 'annuaire_facturation_id',
        'filtres' => [
          'annuaire_facturation' => array (
            array (
              'operateur' => 0,
              'exclu' => 0,
              'blocs' => array (),
              'filtres' =>
              array (
                array (
                  'type_element' => 'annuaire_facturation',
                  'champ_liaison' => NULL,
                  'valeurs' => 'lien_champ|facture_vente.client_id',
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
    'facturation_electronique_flow_id' => [
        'nom' => 'Facturation électronique - Flow ID',
        'lecture_seule' => 1,
        'modification_post_validation' => 1
    ],
  ),
);