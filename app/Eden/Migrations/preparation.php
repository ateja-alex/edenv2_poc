<?php

    return [
		'table_libre' => [
			'nom_table' => "Préparation",
			'nom_table_sql' => "preparation",
			'description' => "",
			'feminin' => "",
			'element' => "Préparation",
			'type_element' => "preparation",
			'element_pluriel' => "Préparations",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			
			'date' => [
				'nom' => "Date",
				'type' => 4,
			],
            'article_id' => [
				'nom' => "Article",
                'type' => 42,
                'type_element_ajax' => 'article',
            ],
            'quantite' => [
				'nom' => "Quantité",
                'type' => 3,
            ],
            'document_id' => [
				'nom' => "Document",
                'type' => 2,
            ],
            'type_element' => [
				'nom' => "Type element",
			],
		],
	];