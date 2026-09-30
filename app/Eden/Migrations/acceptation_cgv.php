<?php

return [
		'table_libre' => [
			'nom_table' => "Acceptations des CGV",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "acceptation CGV",
			'type_element' => "acceptation_cgv",
			'element_pluriel' => "acceptations CGV",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => 'client',
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => "42",
                'type_element_ajax' => 'entite',
			],
			'date' => [
				'nom' => "Date",
				'type' => 5,
			],
			'cgv' => [
				'nom' => "CGV",
				'type' => 7,
			],
			
		],
	];