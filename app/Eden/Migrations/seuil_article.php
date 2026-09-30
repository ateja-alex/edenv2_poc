<?php

return [
		'table_libre' => [
			'nom_table' => "Seuil articles",
			'nom_table_sql' => "seuil_article",
			'description' => "",
			'feminin' => "",
			'element' => "seuil",
			'type_element' => "seuil_article",
			'element_pluriel' => "seuils",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "article",
			],
			'conditionnement_id' => [
				'nom' => "Conditionnement",
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
									'valeurs' => 'lien_champ|seuil_article.article_id',
									'nom_sql' => 'article_id',
									'operateur' => 0,
								),
							),
						),
					), 
				],
			],
			'seuil_mini' => [
				'nom' => "Seuil minimum",
				'type' => 3,
			],
			'seuil_alerte' => [
				'nom' => "Seuil d'alerte",
				'type' => 3,
			],
		],
	];
