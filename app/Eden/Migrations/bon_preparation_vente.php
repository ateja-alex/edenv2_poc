<?php

return [
		'table_libre' => [
			'nom_table' => "Bon preparation clients",
			'nom_table_sql' => "bon_preparation_vente",
			'description' => "",
			'feminin' => "",
			'element' => "bon preparation client",
			'type_element' => "bon_preparation_vente",
			'element_pluriel' => "Bons preparation client",
			'fiche' => 0,
			'vue_sql' => 0,
			
			'disponible_recherche_rapide' => 1,
			'module' => 'Gestion commerciale',
			'creation_rapide' => 0,
			'editable_client' => 1,
			'categorie' => "documents_de_ventes",
			'icone_fontawesome' => "fa-shopping-cart",
			'affichage_recherche' => '<b>#reference_document#</b> - #date#  -  <b>#client_id#</b>',
            'affichage_dans_liste' => '#reference_document#',
			'envoyer_email' => 1,
		],
		'champs_libres' => [
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'recherche' => 1,
				'obligatoire' => 1,
			],
			'projet_id' => [
				'nom' => "Projet",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "projet",
                'index' => true,
			],
			'nature_article_modele' => [
				'nom' => "Modèle de marges par nature",
				'type' => 42,
				'type_element_ajax' => "nature_article_modele",
			],
			'commentaires' => [
				'nom' => "Commentaires",
				'type' => 6,
			],
			'reference_document' => [
				'nom' => "Référence",
				'recherche' => 1,
			],
			'objet' => [
				'nom' => "Objet",
			],
			'frais_de_livraison' => [
				'nom' => "Frais de livraison",
				'type' => 3,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "client",
                'index' => true,
			],
			'montant_document_ht' => [
				'nom' => "HT",
				'type' => 3,
			],
			'montant_document_ttc' => [
				'nom' => "TTC",
				'type' => 3,
			],
			'montant_document_tva' => [
				'nom' => "TVA",
				'type' => 3,
			],
			'valide' => [
				'nom' => "Validé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'livre' => [
				'nom' => "Livré",
				'type' => 20,
				'liste_choix' => 14,
			],
			'canal' => [
				'nom' => "Canal",
				'type' => 20,
				'liste_choix' => 32,
				'modification_post_validation' => 1,
			],
			'client_livraison_id' => [
				'nom' => "Client livré",
				'type' => 42,
				'type_element_ajax' => "client",
			],
			'adresse_de_livraison' => [
				'nom' => "Adresse de livraison",
				'type' => 42,
				'type_element_ajax' => "adresse",
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
									'valeurs' => 'lien_champ|bon_preparation_vente.client_id',
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
			],
			'adresse_de_facturation' => [
				'nom' => "Adresse de facturation",
				'type' => 42,
				'type_element_ajax' => "adresse",
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
									'valeurs' => 'lien_champ|bon_preparation_vente.client_id',
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
			],
			'remise_globale' => [
				'nom' => "Remise globale",
				'type' => 3,
			],
			'remise_globale_type' => [
				'nom' => "Remise globale (type)",
				'type' => 2,
			],
			'responsable_commercial_id' => [
				'nom' => "Responsable commercial",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
				'modification_post_validation' => 1,
			],
			'cacher_totaux_sur_pdf' => [
				'nom' => 'Cacher les totaux dans le document PDF',
				'type' => 20,
				'liste_choix' => 14,
			],
            'pdf' => [
                'nom' => "PDF",
            ],
			'adresse_de_livraison_texte' => [
				'nom' => "Adresse de livraison",
				'type' => 6,
			],
			'adresse_de_facturation_texte' => [
				'nom' => "Adresse de livraison",
				'type' => 6,
			],
			'commentaire_fiche' => [
				'nom' => "Commentaires internes",
				'type' => 6,
			],
			'contacts_ids' => [
				'nom' => "Contacts",
				'type' => 10,
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
									'valeurs' => 'lien_champ|bon_preparation_vente.client_id',
									'nom_sql' => 'client_id',
									'operateur' => 0,
								),
							),
						),
					), 
				],
			],
			'type_modele_document' => [
				'nom' => "Modèle de document",
				'type' => 20,
				'liste_choix' => 86,
				'modification_post_validation' => 1,
			],
			'frais_de_port_saisie' => [
				'nom' => "Frais de port saisie",
				'type' => 3,
			],
			'transforme_en_facture' => [
				'nom' => "Transformé en facture",
				'type' => 20,
				'liste_choix' => 14,
			],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'liste_choix' => 102,
			],
			'modalite_paiement_id' => [
				'nom' => "Modalité de paiement",
				'type' => 20,
				'liste_choix' => 6,
			],
			'compte_bancaire_id' => [
				'nom' => "IBAN",
				'type' => 20,
				'liste_choix' => 4,
			],
            'transporteur' => [
                'nom' => "Transporteur",
                'type' => 1,
            ],
            'numero_de_suivi' => [
                'nom' => "Numéro de suivi",
			],
            'envoye_par_mail' =>[
                'nom' => 'Envoyé par mail',
                'type' => 20,
                'liste_choix' => 14,
            ],
            'catalogue_groupement_id' => [
                'nom' => 'Catalogue de conditions commerciales',
                'type' => 42,
                'type_element_ajax' => 'catalogue_groupement',
                'correspondance_fiche_tiers' => 'catalogue_groupement_id',
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
            ],
			'statut_approbation' => [
				'nom' => 'Statut approbation',
				'type' => 20,
				'liste_choix' => 149,
				'lecture_seule' => 1,
			],
			'marge' => [
				'nom' => "Marge",
				'type' => 3,
			],
			'marge_par_nature' => [
				'nom' => 'Marge par nature',
				'type' => 6,
			],
            'entrepot_id' => [
                'nom' => "Entrepôt",
                'type' => 20,
                'liste_choix' => 8,
            ],
            'devise' => [
				'nom' => 'Devise',
				'type' => 20,
				'liste_choix' => 25,
			],
            'taux_de_change' => [
				'nom' => 'Taux de change',
				'type' => 3,
			],
            'id_recurrence' => [
                'nom' => "Recurrence",
                'type' => 42,
                'type_element_ajax' => 'eden_recurrence_elements'
            ],
            'categorie_comptable_id' => [
                'nom' => 'Catégorie comptable',
                'type' => 42,
                'type_element_ajax' => 'categorie_comptable'
			],
			'ecart_gestion_ttc' => [
				'nom' => "Écart de gestion TTC",
				'type' => 3,
			],
		],
	];