<?php 
 return array (
  'table_libre' => 
  array (
    'nom_table' => 'BL clients',
    'nom_table_sql' => 'bl_vente',
    'element' => 'BL',
    'type_element' => 'bl_vente',
    'element_pluriel' => 'BL',
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
      'type_element' => 'bl_vente',
      'nom' => 'Date',
      'nom_sql' => 'date',
      'type' => 4,
      'recherche' => 1,
      'obligatoire' => 1,
    ),
    'projet_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Projet',
      'nom_sql' => 'projet_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'projet',
      'index' => 1,
    ),
    'nature_article_modele' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Modèle de marges par nature',
      'nom_sql' => 'nature_article_modele',
      'type' => 42,
      'type_element_ajax' => 'nature_article_modele',
    ),
    'commentaires' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Commentaires',
      'nom_sql' => 'commentaires',
      'type' => 6,
    ),
    'reference_document' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Référence',
      'nom_sql' => 'reference_document',
      'recherche' => 1,
    ),
    'objet' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Objet',
      'nom_sql' => 'objet',
    ),
    'frais_de_livraison' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Frais de livraison',
      'nom_sql' => 'frais_de_livraison',
      'type' => 3,
    ),
    'entite_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Entité',
      'nom_sql' => 'entite_id',
      'type' => 42,
      'type_element_ajax' => 'entite',
    ),
    'client_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Client',
      'nom_sql' => 'client_id',
      'type' => 42,
      'recherche' => 1,
      'type_element_ajax' => 'client',
      'index' => 1,
    ),
    'montant_document_ht' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'HT',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ht',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'montant_document_ttc' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'TTC',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_ttc',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'montant_document_tva' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'TVA',
      'format_champ' => 'monetaire_separateur_milliers',
      'nom_sql' => 'montant_document_tva',
      'type' => 3,
      'nombre_decimale' => 2,
    ),
    'valide' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Validé',
      'nom_sql' => 'valide',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'livre' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Livré',
      'nom_sql' => 'livre',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'canal' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Canal',
      'nom_sql' => 'canal',
      'type' => 20,
      'liste_choix' => 32,
      'modification_post_validation' => 1,
    ),
    'client_livraison_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Client livré',
      'nom_sql' => 'client_livraison_id',
      'type' => 42,
      'type_element_ajax' => 'client',
    ),
    'adresse_de_livraison' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison',
      'type' => 42,
      'obligatoire' => 1,
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
									'valeurs' => 'lien_champ|bl_vente.client_id',
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
      'type_element' => 'bl_vente',
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
									'valeurs' => 'lien_champ|bl_vente.client_id',
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
      'type_element' => 'bl_vente',
      'nom' => 'Remise globale',
      'nom_sql' => 'remise_globale',
      'type' => 3,
    ),
    'remise_globale_type' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Remise globale (type)',
      'nom_sql' => 'remise_globale_type',
      'type' => 2,
    ),
    'responsable_commercial_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Responsable commercial',
      'nom_sql' => 'responsable_commercial_id',
      'type' => 42,
      'modification_post_validation' => 1,
      'type_element_ajax' => 'utilisateur',
    ),
    'cacher_totaux_sur_pdf' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Cacher les totaux dans le document PDF',
      'nom_sql' => 'cacher_totaux_sur_pdf',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'pdf' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'PDF',
      'nom_sql' => 'pdf',
    ),
    'adresse_de_livraison_texte' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_livraison_texte',
      'type' => 6,
    ),
    'adresse_de_facturation_texte' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Adresse de livraison',
      'nom_sql' => 'adresse_de_facturation_texte',
      'type' => 6,
    ),
    'commentaire_fiche' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Commentaires internes',
      'nom_sql' => 'commentaire_fiche',
      'type' => 6,
    ),
    'contacts_ids' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Contacts',
      'nom_sql' => 'contacts_ids',
      'type' => 10,
      'type_element_ajax' => 'contact',
      'table_pivot' => 'bl_vente_contacts_ids',
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
									'valeurs' => 'lien_champ|bl_vente.client_id',
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
      'type_element' => 'bl_vente',
      'nom' => 'Modèle de document',
      'nom_sql' => 'type_modele_document',
      'type' => 20,
      'liste_choix' => 86,
      'modification_post_validation' => 1,
    ),
    'frais_de_port_saisie' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Frais de port saisie',
      'nom_sql' => 'frais_de_port_saisie',
      'type' => 3,
    ),
    'transforme_en_facture' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Transformé en facture',
      'nom_sql' => 'transforme_en_facture',
      'type' => 20,
      'liste_choix' => 14,
    ),
    'statut' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Statut',
      'nom_sql' => 'statut',
      'type' => 20,
      'liste_choix' => 102,
    ),
    'modalite_paiement_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Modalité de paiement',
      'nom_sql' => 'modalite_paiement_id',
      'type' => 20,
      'liste_choix' => 6,
    ),
    'compte_bancaire_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'IBAN',
      'nom_sql' => 'compte_bancaire_id',
      'type' => 20,
      'liste_choix' => 4,
    ),
    'transporteur' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Transporteur',
      'nom_sql' => 'transporteur',
      'type' => 1,
    ),
    'numero_de_suivi' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Numéro de suivi',
      'nom_sql' => 'numero_de_suivi',
    ),
    'envoye_par_mail' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Envoyé par mail',
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
      'type_element' => 'bl_vente',
      'nom' => 'Statut approbation',
      'nom_sql' => 'statut_approbation',
      'type' => 20,
      'liste_choix' => 149,
      'lecture_seule' => 1,
    ),
    'marge' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Marge',
      'nom_sql' => 'marge',
      'type' => 3,
    ),
    'entrepot_id' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Entrepôt',
      'nom_sql' => 'entrepot_id',
      'type' => 20,
      'liste_choix' => 8,
    ),
    'marge_par_nature' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Marge par nature',
      'nom_sql' => 'marge_par_nature',
      'type' => 6,
    ),
    'devise' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Devise',
      'nom_sql' => 'devise',
      'type' => 20,
      'liste_choix' => 25,
    ),
    'taux_de_change' => 
    array (
      'type_element' => 'bl_vente',
      'nom' => 'Taux de change',
      'nom_sql' => 'taux_de_change',
      'type' => 3,
    ),
    'id_recurrence' => 
    array (
      'type_element' => 'bl_vente',
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