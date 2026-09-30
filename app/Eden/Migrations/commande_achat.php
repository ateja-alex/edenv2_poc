<?php

return [
		'table_libre' => [
			'nom_table' => "Commandes fournisseurs",
			'nom_table_sql' => "commande_achat",
			'description' => "",
			'feminin' => "e",
			'element' => "commande",
			'type_element' => "commande_achat",
			'element_pluriel' => "commandes",
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
			'date_de_reception' => [
				'nom' => "Date de réception",
				'type' => 4,
				// 'obligatoire' => 1,
				'modification_post_validation' => 1,
			],
			'projet_id' => [
				'nom' => "Projet",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "projet",
                'index' => true,
			],
			'modalite_paiement_id' => [
				'nom' => "Modalité de paiement",
				'type' => 20,
				'liste_choix' => 6,
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
			'valide' => [
				'nom' => "Validée",
				'type' => 20,
				'liste_choix' => 14,
			],
			'facture' => [
				'nom' => "Facturée",
				'type' => 20,
				'liste_choix' => 3,
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
									'valeurs' => 'lien_champ|commande_achat.client_id',
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
			'commentaire_fiche' => [
				'nom' => "Commentaires internes",
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
			'expedie' => [
				'nom' => "Expédiée",
				'type' => 20,
				'liste_choix' => 14,
				'modification_post_validation' => 1,
			],
			'entrepot_id' => [
				'nom' => "Entrepôt",
				'type' => 20,
				'liste_choix' => 8,
			],
			'contacts_ids' => [
				'nom' => "Contacts",
				'type' => 10,
				'type_element_ajax' => 'contact',
			],
			'type_modele_document' => [
				'nom' => "Modèle de document",
				'type' => 20,
				'liste_choix' => 90,
			],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'modification_post_validation' => 1,
				'liste_choix' => 125,
			],
			'id_master' => [
				'nom' => 'Id master'
			],
            'envoye_par_mail' => [
				'nom' => "Envoyée par mail",
				'type' => 20,
				'liste_choix' => 14,
				'modification_post_validation' => 1,
			],
			'frais_de_livraison' => [
				'nom' => "Frais de livraison",
				'type' => 3,
			],
			'frais_de_port_saisie' => [
				'nom' => "Frais de port saisie",
				'type' => 3,
			],
			'a_livrer_chez_client' => [
				'nom' => 'Livraison chez le client',
				'type' => 20,
				'liste_choix' => 14,
			],
			'statut_approbation' => [
				'nom' => 'Statut approbation',
				'type' => 20,
				'liste_choix' => 149,
				'lecture_seule' => 1,
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
                'type_element_ajax' => "client",
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
		],
	];
