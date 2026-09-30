<?php

return [
		'table_libre' => [
			'nom_table' => "articles facturés",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'type_element' => "facture_vente_lignes",
		    'element' => 'article facturé',
		    'element_pluriel' => 'articles facturés',
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
            'article_id' => [
                'nom' => "Article",
                'type' => 42,
                'type_element_ajax' => 'article',
                'index' => true,
            ],
            'document_id' => [
                'nom' => "Facture",
                'type' => 42,
                'type_element_ajax' => 'facture_vente',
                'index' => true,
            ],
            'client_id_ligne' => [
                'nom' => "Client",
                'type' => 42,
                'type_element_ajax' => 'client',
            ],
            'ligne' => [
                'nom' => "Ligne",
                'type' => 2,
            ],
            'designation' => [
                'nom' => "Désignation",
            ],
            'tarif' => [
                'nom' => "Tarif",
                'type' => 3,
            ],
            'quantite' => [
                'nom' => "Quantité",
                'type' => 3,
            ],
            'remise' => [
                'nom' => "Remise",
                'type' => 3,
            ],
            'tva' => [
                'nom' => "Tva",
                'type' => 3,
            ],
            'type_tarif' => [
                'nom' => "Type tarif",
                'type' => 2,
            ],
            'remise_globale_ligne' => [
                'nom' => 'Remise globale',
                'type' => 3,
            ],
            'tarif_saisi' => [
                'nom' => 'Tarif saisi',
                'type' => 3,
            ],
			'tarif_force' => [
				'nom' => 'Tarif forcé',
				'type' => 3,
			],
            'prix_achat_force' => [
                'nom' => "Prix d'achat forcé",
                'type' => 3,
            ],
            'style_ligne_document_id' => [
                'nom' => "Style ligne",
                'type' => 42,
                'type_element_ajax' => 'style_ligne_document',
            ],
            'afficher_photo' => [
                'nom' => "Afficher photo",
                'type' => 20,
                'liste_choix' => 14,
            ],
            'code_article' => [
                'nom' => 'Code Article',
            ],
            'prix_achat' => [
                'nom' => 'Prix achat',
                'type' => 3,
            ],
            'description' => [
                'nom' => 'Description',
                'type' => 6,
            ],
            'coefficient' => [
                'nom' => "Coefficient",
                'type' => 6,
            ],
            'coefficient_article' => [
                'nom' => "Coefficient par article",
                'type' => 6,
            ],
            'coefficient_regroupement' => [
                'nom' => "Coefficient par regroupement",
                'type' => 6,
            ],
            'coefficient_devis' => [
                'nom' => "Coefficient par document",
                'type' => 6,
            ],
            'numero_de_serie' => [
                'nom' => 'Numéro de série',
            ],
            'numeros_de_lot' => [
                'nom' => 'Numéro de lot',
            ],
            'unite' => [
                'nom' => 'Unité',
                'type' => 42,
                'type_element_ajax' => 'article_unite',
            ],
            'disponibilite' => [
                'nom' => 'Disponibilité',
            ],
            'declinaison_id' => [
                'nom' => 'Déclinaison',
                'type' => 42,
                'type_element_ajax' => 'article_declinaison',
            ],
            'tarif_apres_remise' => [
                'nom' => 'Tarif après remise',
                'type' => 3,
            ],
            'conditionnement' => [
                'nom' => 'Conditionnement',
                'type' => 42,
                'type_element_ajax' => 'conditionnement'
            ],
            'calculateur' => [
                'nom' => 'Calculateur',
                'type' => 6,
            ],
            'tarif_net' => [
                'nom' => 'Tarif net',
                'type' => 3,
            ],
            'total' => [
                'nom' => 'Tarif total',
                'type' => 3,
            ],
            'marge_pourcentage' => [
				'nom' => 'Marge (%)',
				'type' => 3,
			],
			'type_element_source' => [
				'nom' => 'Type document origine',
				'type' => 0,
			],
			'id_element_source' => [
				'nom' => 'ID document origine',
				'type' => 2,
			],
			'id_ligne_source' => [
				'nom' => 'ID ligne origine',
				'type' => 2,
			],
			'transforme' => [
				'nom' => 'Traité',
				'type' => 20,
				'liste_choix' => 302,
			],
			'transforme_reliquat' => [
				'nom' => 'Reliquat',
				'type' => 3,
			],
            'regroupement_id' => [
                'nom' => 'ID regroupement',
                'type' => 2,
            ],
            'couleur_regroupement' => [
                'nom' => 'Couleur regroupement',
                'type' => 9,
            ],
			'nomenclature_ligne_parent' => [
				'nom' => 'Ligne parent nomenclature',
				'type' => 42,
				'type_element_ajax' => 'facture_vente_lignes',
			],
            'valide' => [
				'nom' => "Valide",
				'type' => 20,
				'liste_choix' => 14,
			],
            'avancement_actuel' => [
				'nom' => "Avancement actuel",
				'type' => 3,
			],
            'avancement_precedent' => [
				'nom' => "Avancement précédent",
				'type' => 3,
			],
            'avancement_pourcentage' => [
				'nom' => "Avancement (%)",
				'type' => 3,
			],
            'tarif_eco_contribution' => [
                'nom' => "Tarif d'éco-contribution",
                'type' => 3
            ],
            'application_eco_contribution' => [
                'nom' => "Application d'éco-contribution",
                'type' => 20,
                'liste_choix' => 635
            ],
            'categorie_eco_contribution_id' => [
                'nom' => "Catégorie d'éco-contribution",
                'type' => 42,
                'type_element_ajax' => 'categorie_eco_contribution'
            ],
            'quantite_unite_eco_contribution' => [
                'nom' => "Quantité d'unité de l'éco-contribution",
                'type' => 3,
            ],
            'utilisateur_id' => [
                'nom' => 'Utilisateur',
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
            ],
            'categorie_comptable_article_id' => [
                'nom' => 'Catégorie comptable article',
                'type' => 42,
                'type_element_ajax' => 'categorie_comptable_article',
                'filtres' => [
                    'categorie_comptable_article' => array (
                        array (
                            'operateur' => 0,
                            'exclu' => 0,
                            'blocs' => array (),
                            'filtres' => 
                            array (
                                array (
                                    'type_element' => 'categorie_comptable_article',
                                    'champ_liaison' => NULL,
                                    'valeurs' => 'lien_champ|facture_vente_lignes.article_id',
                                    'nom_sql' => 'article_id',
                                    'operateur' => 0,
                                ),
                            ),
                        ),
                    ), 
                ],
            ]
		],
	];