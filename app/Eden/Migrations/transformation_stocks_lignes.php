<?php

return [
		'table_libre' => [
			'nom_table' => "Transformation des Stocks",
			'nom_table_sql' => "transformation_stocks_lignes",
			'description' => "",
			'feminin' => "",
			'element' => "transformation_lignes",
			'type_element' => "transformation_stocks_lignes",
			'element_pluriel' => "transformations_lignes",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
		],
		'champs_libres' => [
			'transformation_stocks_id' => [
				'nom' => "Article",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "transformation_stocks",
            ],
			'conditionnement_id' => [
				'nom' => "Conditionnement id",
				'type' => 42,
				'type_element_ajax' => "conditionnement",
			],
			'quantite_depart' => [
				'nom' => "Quantité de départ",
				'type' => 3,
            ],
            'quantite_arrive' => [
                'nom' => "Quantité d'arrivée",
                'type' => 3,
            ],
		],
	];