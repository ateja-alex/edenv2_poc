<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'Commandes',
    'nom_table_sql' => 'commande_vente',
    'feminin' => 'e',
    'element' => 'commande',
    'type_element' => 'commande_vente',
    'element_pluriel' => 'commandes',
    'disponible_recherche_rapide' => 1,
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
      'type_element' => 'commande_vente',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
      'valeur_defaut' => '#aujourdhui#',
    ),
    'date_changement_statut' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Facturée le',
      'nom_sql' => 'date_changement_statut',
      'type' => 4,
    ),
    'projet_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'projet',
      'index' => 1,
    ),
    'nature_article_modele' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Modèle de marges par nature',
      'nom_sql' => 'nature_article_modele',
      'type' => 42,
      'type_element_ajax' => 'nature_article_modele',
    ),
    'commentaires' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Commentaires',
      'nom_sql' => 'commentaires',
      'type' => 6,
    ),
    'reference_document' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Référence',
      'nom_sql' => 'reference_document',
      'recherche' => 1,
    ),
    'objet' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Objet',
      'nom_sql' => 'objet',
    ),
    'entite_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'client_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'client',
      'index' => 1,
    ),
    'montant_document_ht' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'HT',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ht',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'regle' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Réglée',
      'nom_sql' => 'regle',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'montant_document_ttc' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'TTC',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ttc',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'montant_document_tva' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'TVA',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_tva',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'montant_document_reliquat' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Total reliquat',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_reliquat',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'montant_document_marge_reliquat' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Marge reliquat',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_marge_reliquat',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'frais_de_livraison' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Frais de livraison',
      'nom_sql' => 'frais_de_livraison',
      'type' => 3,
    ),
    'valide' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Validée',
      'nom_sql' => 'valide',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'facture' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Facturée',
      'nom_sql' => 'facture',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'annule' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Annulé',
      'nom_sql' => 'annule',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'motif_annulation' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Motif d\'annulation',
      'format_champ' => 'wysiwyg',
      'nom_sql' => 'motif_annulation',
      'type' => 6,
    ),
    'canal' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Canal',
      'nom_sql' => 'canal',
      'type' => 20,
      'liste_choix' => 32,
      'modification_post_validation' => 1,
    ),
    'client_livraison_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Client livré',
      'nom_sql' => 'client_livraison_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'commentaire_fiche' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Commentaires internes',
      'nom_sql' => 'commentaire_fiche',
      'type' => 6,
    ),
    'expedie' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Expédiée',
      'nom_sql' => 'expedie',
      'type' => 20,
      'liste_choix' => 14,
      'modification_post_validation' => 1,
    ),
    'solde_document_ttc' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Solde document TTC',
      'nom_sql' => 'solde_document_ttc',
      'type' => 3,
    ),
    'adresse_de_livraison' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison',
      'type' => 42,
      'obligatoire' => 0,
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
                'valeurs' => 'lien_champ|commande_vente.client_id',
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
      'type_element' => 'commande_vente',
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
                'valeurs' => 'lien_champ|commande_vente.client_id',
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
      'type_element' => 'commande_vente',
      'nom' => 'Remise globale',
      'nom_sql' => 'remise_globale',
      'type' => 3,
    ),
    'remise_globale_type' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Remise globale (type)',
      'nom_sql' => 'remise_globale_type',
      'type' => 2,
    ),
    'modalite_paiement_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Modalité de paiement',
      'nom_sql' => 'modalite_paiement_id',
      'type' => 20,
      'liste_choix' => 6,
    ),
    'compte_bancaire_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'IBAN',
      'nom_sql' => 'compte_bancaire_id',
      'type' => 20,
      'liste_choix' => 4,
    ),
    'responsable_commercial_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Responsable commercial',
      'nom_sql' => 'responsable_commercial_id',
      'type' => 42,
      'modification_post_validation' => 1,
      'valeur_defaut' => '#utilisateur_connecte#',
      'type_element_ajax' => 'utilisateur',
      'desactiver_creation_a_la_volee' => 1,
    ),
    'cacher_totaux_sur_pdf' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Cacher les totaux dans le document PDF',
      'nom_sql' => 'cacher_totaux_sur_pdf',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'pdf' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'PDF',
      'nom_sql' => 'pdf',
    ),
    'adresse_de_livraison_texte' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison_texte',
      'type' => 6,
    ),
    'adresse_de_facturation_texte' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_facturation_texte',
      'type' => 6,
    ),
    'entrepot_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Entrepôt',
      'nom_sql' => 'entrepot_id',
      'type' => 20,
      'liste_choix' => 8,
    ),
    'contacts_ids' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Contacts',
      'nom_sql' => 'contacts_ids',
      'type' => 10,
      'type_element_ajax' => 'contact',
      'table_pivot' => 'commande_vente_contacts_ids',
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
                          'valeurs' => 'lien_champ|commande_vente.client_id',
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
      'type_element' => 'commande_vente',
      'nom' => 'Modèle de document',
      'nom_sql' => 'type_modele_document',
      'type' => 20,
      'liste_choix' => 93,
      'modification_post_validation' => 1,
    ),
    'transforme_en_facture' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Transformé en facture',
      'nom_sql' => 'transforme_en_facture',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'numero_commande_client' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Numéro commande client',
      'nom_sql' => 'numero_commande_client',
    ),
    'frais_de_port_saisie' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Frais de port saisie',
      'nom_sql' => 'frais_de_port_saisie',
      'type' => 3,
    ),
    'statut' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 101,
    ),
    'commande_fournisseur_realisee' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Articles commandés',
      'nom_sql' => 'commande_fournisseur_realisee',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'commande_fournisseur_recue' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Articles prêts',
      'nom_sql' => 'commande_fournisseur_recue',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'envoye_par_mail' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Envoyée par mail',
      'nom_sql' => 'envoye_par_mail',
      'type' => 20,
      'liste_choix' => 14,
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
    'statut_approbation' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Statut approbation',
      'nom_sql' => 'statut_approbation',
      'type' => 20,
      'liste_choix' => 149,
      'lecture_seule' => 1,
    ),
    'coupon_reduction' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Coupon réduction',
      'nom_sql' => 'coupon_reduction',
      'type' => 1,
    ),
    'marge' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Marge',
      'nom_sql' => 'marge',
      'type' => 3,
    ),
    'marge_par_nature' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Marge par nature',
      'nom_sql' => 'marge_par_nature',
      'type' => 6,
    ),
    'devise' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Devise',
      'nom_sql' => 'devise',
      'type' => 20,
      'liste_choix' => 25,
    ),
    'taux_de_change' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Taux de change',
      'nom_sql' => 'taux_de_change',
      'type' => 3,
    ),
    'contact_id' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Contact',
      'nom_sql' => 'contact_id',
      'type' => 42,
      'type_element_ajax' => 'contact',
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
                          'valeurs' => 'lien_champ|commande_vente.client_id',
                          'nom_sql' => 'client_id',
                          'operateur' => 0,
                      ),
                  ),
              ),
          ), 
      ],
    ),
    'date_livraison' => 
    array (
      'type_element' => 'commande_vente',
      'nom' => 'Date prévisionnelle de livraison',
      'nom_sql' => 'date_livraison',
      'type' => 4,
    ),
    'id_recurrence' => 
    array (
      'type_element' => 'commande_vente',
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
  ),
);