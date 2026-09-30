<?php

return [
		'table_libre' => [
			'nom_table' => "Ape",
			'nom_table_sql' => "ape",
			'description' => "",
			'feminin' => "",
			'element' => "ape",
			'type_element' => "ape",
			'element_pluriel' => "ape",
			'fiche' => 0,
			'parametre' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#, #description#',
		],
		'champs_libres' => [
			'type' => [
				'nom' => "Type",
			],
            'code' => [
                'nom' => "Code",
                'recherche' => "1",
            ],
            'description' => [
                'nom' => "Description",
                'recherche' => "1",
            ],
		],
	];