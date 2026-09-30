<?php

return [
		'table_libre' => [
			'nom_table' => "Transformation des Stocks",
			'nom_table_sql' => "transformation_stocks",
			'description' => "",
			'feminin' => "",
			'element' => "transformation_stocks",
			'type_element' => "transformation_stocks",
			'element_pluriel' => "transformations_stocks",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
		],
		'champs_libres' => [
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "article",
			],
			'date_transformation' => [
				'nom' => "Date de transformation",
                'type' => 4,
				'obligatoire' => 1,
			],
			'entrepot_id' => [
				'nom' => "Entrepot",
				'type' => 20,
				'liste_choix' => 8,
			],
			'conditionnement_depart_id' => [
				'nom' => "Conditionnement de départ",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "conditionnement",
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
									'valeurs' => 'lien_champ|transformation_stocks.article_id',
									'nom_sql' => 'article_id',
									'operateur' => 0,
								),
							),
						),
					), 
				],
			],
			'quantite_conditionnement_depart' => [
				'nom' => "Quantité Conditionnement de départ",
				'type' => 3,
			],
			'quantite_colisee' => [
				'nom' => "Quantité colisée",
				'type' => 3,
			],
		],
	];