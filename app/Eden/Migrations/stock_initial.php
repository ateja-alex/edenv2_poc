<?php

return [
		'table_libre' => [
			'nom_table' => "Stock Initiaux",
			'nom_table_sql' => "stock_initial",
			'description' => "",
			'feminin' => "",
			'element' => "stock intial",
			'type_element' => "stock_initial",
			'element_pluriel' => "stocks initiaux",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'entrepot_id' => [
				'nom' => "Entrepot",
				'type' => 20,
				'liste_choix' => 8,
			],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "article",
			],
			'stock_initial' => [
				'nom' => "Stock intial",
				'type' => 3,
				'obligatoire' => 1,
			],
			'unite_id' => [
				'nom' => "Unité",
				'type' => 3,
			],
			'conditionnement_id' => [
				'nom' => "Conditionnement",
				'type' => 42,
				'type_element_ajax' => 'conditionnement',
			],
			'quantite_conditionnement' => [
				'nom' => "Quantité Conditionnement",
				'type' => 3,
			],
            'type_conditionnement' => [
                'nom' => "Type Conditionnement",
            ],
            'prix_achat' => [
                'nom' => "PU Achat",
                'type' => 3,
            ],
            'adressage' => [
                'nom' => "Adressage",
            ],
		],
	];