<?php

return [
		'table_libre' => [
			'nom_table' => "Acomptes fournisseurs",
			'nom_table_sql' => "acompte_achat",
			'description' => "",
			'feminin' => "",
			'element' => "acompte",
			'type_element' => "acompte_achat",
			'element_pluriel' => "acomptes",
			'fiche' => 0,
			'module' => 'Gestion commerciale',

			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
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
			'date_changement_statut' => [
				'nom' => "Réglée le",
				'type' => 4,
			],
			'date_de_reglement' => [
				'nom' => "Date règlement",
				'type' => 4,
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
			'compte_bancaire_id' => [
				'nom' => "IBAN",
				'type' => 20,
				'liste_choix' => 4,
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
			'objet' => [
				'nom' => "Objet",
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => "42",
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
			'regle' => [
				'nom' => "Réglé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'canal' => [
				'nom' => "Canal",
				'type' => 20,
				'liste_choix' => 32,
				'modification_post_validation' => 1,
			],
			'solde_document_ttc' => [
				'nom' => "Solde document TTC",
				'type' => 3,
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
									'valeurs' => 'lien_champ|acompte_achat.client_id',
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
			'acompte' => [
				'nom' => "Acompte",
				'type' => 3,
			],
			'acompte_type' => [
				'nom' => "Acompte (type)",
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
			'annule' => [
				'nom' => "Annulé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'commentaire_fiche' => [
				'nom' => "Commentaires internes",
				'type' => 6,
			],
			'contacts_ids' => [
				'nom' => "Contacts",
				'type' => 10,
				'type_element_ajax' => 'contact',
			],
			'type_modele_document' => [
				'nom' => "Modèle de document",
				'type' => 20,
				'liste_choix' => 83,
			],
            'envoye_par_mail' => [
				'nom' => "Envoyé par mail",
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
			'comptabilise' => [
				'nom' => "Comptabilisé",
				'type' => 20,
				'liste_choix' => 14,
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
            'annulee_par_avoir' => [
                'nom' => "Annulé par avoir",
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
