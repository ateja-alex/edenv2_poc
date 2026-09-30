<?php

return [
		'table_libre' => [
			'nom_table' => "Paramétrage avancé des documents",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "paramétrage",
			'type_element' => "parametrage_avance_des_documents",
			'element_pluriel' => "paramétrages",
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
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'type_element' => [
				'nom' => "Type de document",
			],
			'exemplaires' => [
				'nom' => "Exemplaires",
				'type' => 2,
			],
			'modele' => [
				'nom' => "Modèle",
			],
			'champs_obligatoires' => [
				'nom' => "Champs obligatoires",
				'type' => 6,
			],
			
		],
	];