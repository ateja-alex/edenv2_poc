<?php

return [
		'table_libre' => [
			'nom_table' => "Pivot articles fournisseurs",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "pivot article fournisseur",
			'type_element' => "article_fournisseur",
			'element_pluriel' => "pivots articles fournisseurs",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
			'affichage_recherche' => '#fournisseur_id# - #article_id#',
			'affichage_fiche_type' => '#fournisseur_id# - #article_id#',
			'affichage_dans_liste' => '#fournisseur_id# - #article_id#',
			'affichage_pour_select' => '#fournisseur_id# - #article_id#',
		],
		'champs_libres' => [
            'nom' => [
                'nom' => "Nom",
            ],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => "article",
				'obligatoire' => 1,
			],
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'type_element_ajax' => "fournisseur",
				'obligatoire' => 1,
			],
			'fournisseur_prioritaire' => [
				'nom' => "Fournisseur principal",
				'type' => 20,
				'liste_choix' => 14,
			],
			'reference' => [
				'nom' => "Référence",
			],
			'designation' => [
				'nom' => "Désignation",
			],
			'disponibilite' => [
				'nom' => "Disponibilité",
			],
			'conditionnement_id' => [
				'nom' => "Conditionnement",
                'type' => 42,
                'type_element_ajax' => 'conditionnement',
                'format_champ' => "",
				'filtres' => [
					'conditionnement' => array (
						array (
							'operateur' => 0,
							'exclu' => 0,
							'blocs' => array (),
							'filtres' => 
							array (
								array (
									'type_element' => 'conditionnement',
									'champ_liaison' => NULL,
									'valeurs' => 'lien_champ|article_fournisseur.article_id',
									'nom_sql' => 'article_id',
									'operateur' => 0,
								),
							),
						),
					), 
				],
			],
			'unite' => [
				'nom' => "Unités",
				'type' => 20,
				'liste_choix' => 81,
			],
		],
	];