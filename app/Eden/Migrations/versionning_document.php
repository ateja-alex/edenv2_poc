<?php

return [
		'table_libre' => [
			'nom_table' => "Versionning des documents",
			'nom_table_sql' => "versionning_document",
			'description' => "",
			'feminin' => "e",
			'element' => "Versionning des documents",
			'type_element' => "versionning_document",
			'element_pluriel' => "Versionning des documents",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'id_document' => [
				'nom' => "ID Document",
            ],
            'type_element' => [
				'nom' => "Type element",
			],
            'url_document' => [
                'nom' => "Url document",
                
            ],
			'montant' => [
				'nom' => "Montant",
				'type' => 3,
			],
			'date' => [
				'nom' => "Date",
				'type' => 5,
			],
			'reference' => [
				'nom' => "Référence",
			],
			'hash_contenu' => [
				'nom' => "Hash contenu",
			],
		],
	];