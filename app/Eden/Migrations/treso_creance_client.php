<?php

return [
		'table_libre' => [
			'nom_table' => "Creances Clients",
			'nom_table_sql' => "treso_creance_client",
			'description' => "",
			'feminin' => "",
			'element' => "Creance client",
			'type_element' => "treso_creance_client",
			'element_pluriel' => "Creances clients",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'client' => [
				'nom' => "Client",
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],
			'montants_mensuels' => [
				'nom' => "Montants mensuels",
				'type' => 6,
			],
			'entite_id' => [
				'nom' => "entite_id",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
		],
	];