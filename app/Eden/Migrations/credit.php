<?php

return [
		'table_libre' => [
			'nom_table' => "Crédits",
			'nom_table_sql' => "credit",
			'description' => "",
			'feminin' => "",
			'element' => "crédit",
			'type_element' => "credit",
			'element_pluriel' => "crédits",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'client_id' => [
				'nom' => "Client",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'client',
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'origine' => [
				'nom' => "Origine",
				'type' => 20,
				'liste_choix' => 42,
			],
			'date' => [
				'nom' => "Date",
				'obligatoire' => 1,
				'type' => 4,
			],
			'nombre' => [
				'nom' => "Nombre",
				'obligatoire' => 1,
				'type' => 2,
			],
			'source_type_element' => [
				'nom' => "Source type element",
			],
			'source_element_id' => [
				'nom' => "Source ID element",
			],
		],
	];