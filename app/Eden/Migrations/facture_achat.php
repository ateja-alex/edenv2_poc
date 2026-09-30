<?php

return [
		'table_libre' => [
			'nom_table' => "Factures fournisseurs",
			'nom_table_sql' => "facture_achat",
			'description' => "",
			'feminin' => "e",
			'element' => "facture",
			'type_element' => "facture_achat",
			'element_pluriel' => "factures",
			'fiche' => 0,

			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
			'editable_client' => 1,
			'categorie' => "documents_d_achats",
			'icone_fontawesome' => "fa-shopping-cart",
			'affichage_recherche' => '<b>#reference_document#</b> - #date#  -  <b>#fournisseur_id#</b>',
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
			'modalite_paiement_id' => [
				'nom' => "Modalité de paiement",
				'type' => 20,
				'liste_choix' => 6,
			],
			'mode_paiement_id' => [
				'nom' => "Mode de paiement",
				'type' => 20,
				'liste_choix' => 7,
			],
			'projet_id' => [
				'nom' => "Projet",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "projet",
                'index' => true,
			],
			'commentaires' => [
				'nom' => "Commentaires",
				'type' => 6,
			],
			'reference_document' => [
				'nom' => "Référence",
				'recherche' => 1,
			],
			'date_de_reglement' => [
				'nom' => "Date règlement",
				'type' => 4,
			],
			'objet' => [
				'nom' => "Objet",
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "fournisseur",
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
			'solde_document_ttc' => [
				'nom' => "Solde TTC",
				'type' => 3,
			],
			'comptabilise' => [
				'nom' => "Comptabilisée",
				'type' => 20,
				'liste_choix' => 14,
			],
			'valide' => [
				'nom' => "Validée",
				'type' => 20,
				'liste_choix' => 14,
			],
			'annule' => [
				'nom' => "Annulée",
				'type' => 20,
				'liste_choix' => 14,
			],
			'regle' => [
				'nom' => "Réglée",
				'type' => 20,
				'liste_choix' => 14,
			],
            'adresse_de_livraison' => [
                'nom' => "Adresse de livraison",
                'type' => 42,
                'type_element_ajax' => 'adresse_interne',
            ],
            'adresse_de_livraison_client' => [
                'nom' => "Adresse de livraison client",
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
									'valeurs' => 'lien_champ|facture_achat.client_id',
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
            'adresse_de_livraison_texte' => [
                'nom' => "Adresse de livraison texte",
                'type' => 6,
            ],
            'adresse_de_facturation' => [
                'nom' => "Adresse de facturation",
                'type' => 42,
                'type_element_ajax' => 'adresse_interne',
            ],
            'adresse_de_facturation_texte' => [
                'nom' => "Adresse de facturation texte",
                'type' => 6,
            ],
			'remise_globale' => [
				'nom' => "Remise globale",
				'type' => 3,
			],
			'remise_globale_type' => [
				'nom' => "Remise globale (type)",
				'type' => 2,
			],
            'pdf' => [
                'nom' => "PDF",
            ],
			'annulee_v1' => [
				'nom' => "Annulé v1",
				'type' => 20,
				'liste_choix' => 14,
			],
			'pdf_fournisseur' => [
				'nom' => "PDF Fournisseur",
				'type' => 7,
			],
			'commentaire_fiche' => [
				'nom' => "Commentaires internes",
				'type' => 6,
			],
            'reference_commande' => [
                'nom' => 'Référence commande',
            ],
            'reference_fournisseur' => [
                'nom' => 'Référence fournisseur',
            ],
            'date_changement_statut' => [
                'nom' => "Date changement de statut",
                'type' => 4,
            ],
			'contacts_ids' => [
				'nom' => "Contacts",
				'type' => 10,
				'type_element_ajax' => 'contact',
			],
			'type_modele_document' => [
				'nom' => "Modèle de document",
				'type' => 20,
				'liste_choix' => 92,
			],
            'envoye_par_mail' => [
				'nom' => "Envoyée par mail",
				'type' => 20,
				'liste_choix' => 14,
				'modification_post_validation' => 1,
			],
			'statut_approbation' => [
				'nom' => 'Statut approbation',
				'type' => 20,
				'liste_choix' => 149,
				'lecture_seule' => 1,
			],
			'facture_scannee' => [
				'nom' => 'Facture scannée',
				'type' => 7,
				'modification_post_validation' => 1,
			],
			'annulee_par_avoir' => [
				'nom' => "Annulée par avoir",
				'type' => 20,
				'liste_choix' => 14,
				'modification_post_validation' => 1,
			],
			'avoir_partiel' => [
				'nom' => "Avoir partiel",
				'type' => 20,
				'liste_choix' => 14,
				'modification_post_validation' => 1,
			],
			'avoir_total' => [
				'nom' => "Avoir total",
				'type' => 20,
				'liste_choix' => 14,
				'modification_post_validation' => 1,
			],
			'frais_de_livraison' => [
				'nom' => "Frais de livraison",
				'type' => 3,
			],
			'livraison_client' => [
				'nom' => "Client livré",
				'type' => 42,
				'type_element_ajax' => "client",
			],
            'a_livrer_chez_client' => [
                'nom' => 'Livraison chez le client',
                'type' => 20,
                'liste_choix' => 14,
            ],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'modification_post_validation' => 1,
				'liste_choix' => 128,
			],
			'marge_par_nature' => [
				'nom' => 'Marge par nature',
				'type' => 6,
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
            'client_id' => [
                'nom' => "Client",
                'type' => 42,
                'recherche' => 1,
                'type_element_ajax' => "client",
                'index' => true,
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
				'correspondance_fiche_tiers' => 'catalogue_groupement_id',
			],
			'facturation_electronique_achat_id' => [
				'nom' => 'Facturation électronique achat',
				'type' => 42,
				'type_element_ajax' => 'facturation_electronique_achat',
				'modification_post_validation' => 1,
			],
		],
	];
