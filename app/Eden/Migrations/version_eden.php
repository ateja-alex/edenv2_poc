<?php

return [
		'table_libre' => [
			'nom_table' => "Version Eden",
			'nom_table_sql' => "version_eden",
			'description' => "",
			'feminin' => "e",
			'element' => "Version Eden",
			'type_element' => "version_eden",
			'element_pluriel' => "Versions Eden",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'numero_version' => [
				'nom' => "Version",
                'recherche' => 1,
            ],
            'titre' => [
				'nom' => "Titre",
                'recherche' => 1,
			],
            'description' => [
                'nom' => "Description",
                'type' => 6,
                'recherche' => 1,
            ],
            'date_ajout' => [
                'nom' => 'Date ajout',
                'type' => 4,
            ],
		],
	];