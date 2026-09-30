<?php 

return [
		'table_libre' => [
			'nom_table' => "article_conditions_commerciales",
			'nom_table_sql' => "article_conditions_commerciales",
			'description' => "",
			'feminin' => "",
			'element' => "article_conditions_commerciales",
			'type_element' => "article_conditions_commerciales",
			'element_pluriel' => "article_conditions_commerciales",
			'fiche' => "0",
            'vue_sql' => "1",
		],
		'champs_libres' => [
			'article_id' => [
				'nom' => "Article",
				'type' => "42",
                'type_element_ajax' => "article",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "article_id",
			],
			'client_id' => [
				'nom' => "Client",
				'type' => "42",
				'type_element_ajax' => "client",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "client_id",
			],
			'catalogue_tarif_id' => [
				
				'nom' => "Catalogue tarif",
				'type' => "42",
				'type_element_ajax' => "catalogue_tarif",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "catalogue_tarif_id",
			],
			'palier_quantite' => [
				'nom' => "Palier quantité",
				'type' => "3",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "palier_quantite",
			],
			'tarif' => [
				'nom' => "Tarif",
				'type' => "3",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "tarif",
			],
			'remise' => [
				'nom' => "Remise",
				'type' => "3",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "remise",
			],
			'prix_achat' => [
				'nom' => "Prix achat",
				'type' => "3",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "prix_achat",
			],
			'famille_id' => [
				'nom' => "Famille",
				'type' => "42",
				'type_element_ajax' => "famille",
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "famille_id",
			],
			'conditionnement' => [
				'nom' => "Conditionnement",
				'type' => "42",
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
									'valeurs' => 'lien_champ|article_conditions_commerciales.article_id',
									'nom_sql' => 'article_id',
									'operateur' => 0,
								),
							),
						),
					), 
				],
				'type_element_origine' => "condition_commerciale",
				'nom_sql_origine' => "conditionnement",
			],
		],
		'champs_libres_supprimes' => [
		],
	];