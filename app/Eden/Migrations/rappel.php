<?php

return [
		'table_libre' => [
			'nom_table' => "Rappels",
			'nom_table_sql' => "rappel",
			'description' => "",
			'feminin' => "",
			'element' => "rappel",
			'type_element' => "rappel",
			'element_pluriel' => "rappels",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => "client",
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'titre' => [
				'nom' => "Titre",
				'obligatoire' => 1,
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'obligatoire' => 0,
			],
			'affectation' => [
				'nom' => "Affectation",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			'termine' => [
				'nom' => "Terminé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'deadline' => [
				'nom' => "Deadline",
				'type' => 4,
				'obligatoire' => 0,
			],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'liste_choix' => 35,
			],
		],
	];