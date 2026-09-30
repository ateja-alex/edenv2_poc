<?php

return [
		'table_libre' => [
			'nom_table' => "Composition article",
			'nom_table_sql' => "composition_article",
			'description' => "",
			'feminin' => "",
			'element' => "composition article",
			'type_element' => "composition_article",
			'element_pluriel' => "compositions article",
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
			'tarif' => [
				'nom' => "Tarif",
				'type' => 3,
			],
			'prix_achat' => [
				'nom' => "Prix d'achat",
				'type' => 3,
			],
            'conditionnement' => [
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
									'valeurs' => 'lien_champ|composition_article.article_enfant_id',
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