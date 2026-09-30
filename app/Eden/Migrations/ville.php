<?php

return [
		'table_libre' => [
			'nom_table' => "Villes",
			'nom_table_sql' => "ville",
			'description' => "",
			'feminin' => "",
            'element' => "ville",
            'type_element' => "ville",
            'element_pluriel' => "villes",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'nom' => [
                'nom' => "Nom",
            ],
            'pays_id' => [
                'nom' => "Pays",
                'type' => 42,
                'type_element_ajax' => 'pays',
            ],
			'region' => [
				'nom' => "Region",
			],
            'departement' => [
                'nom' => "Département",
            ],
            'sous_prefecture' => [
                'nom' => "Sous préfecture",
            ],
            'canton' => [
                'nom' => "Canton",
            ],
            'code_postal' => [
                'nom' => "Code postal",
            ],
            'lattitude' => [
                'nom' => "Lattitude",
                'type' => 3,
            ],
            'longitude' => [
                'nom' => "Longitude",
                'type' => 3,
            ],
		],
	];