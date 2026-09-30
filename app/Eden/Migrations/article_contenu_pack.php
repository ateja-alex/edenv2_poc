<?php

return [
		'table_libre' => [
			'nom_table' => "Article contenu pack",
			'nom_table_sql' => "article_contenu_pack",
			'description' => "",
			'feminin' => "",
			'element' => "article contenu pack",
			'type_element' => "article_contenu_pack",
			'element_pluriel' => "articles contenus pack",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
                'obligatoire' => 1,
			],
			'article_enfant_id' => [
				'nom' => "Article enfant",
				'type' => 42,
				'type_element_ajax' => 'article',
                'obligatoire' => 1,
			],
			'quantite' => [
				'nom' => "Quantité",
				'type' => 3,
                'obligatoire' => 1,
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],
            'conditionnement_id' => [
                'nom' => "Conditionnement",
				'type' => 42,
				'type_element_ajax' => 'conditionnement',
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
									'valeurs' => 'lien_champ|article_contenu_pack.article_enfant_id',
									'nom_sql' => 'article_id',
									'operateur' => 0,
								),
							),
						),
					), 
				],
            ],
        ]
    ];