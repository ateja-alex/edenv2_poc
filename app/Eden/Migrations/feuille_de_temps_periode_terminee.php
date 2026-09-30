<?php

return [
		'table_libre' => [
			'nom_table' => "Feuilles de temps : périodes terminées",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "feuille de temps : période terminée",
			'type_element' => "feuille_de_temps_periode_terminee",
			'element_pluriel' => "feuille de temps : périodes terminées",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'date_debut' => [
				'nom' => "Date de début",
				'type' => 4,
				'obligatoire' => 1,
			],
            'date_fin' => [
				'nom' => "Date de fin",
				'type' => 4,
				'obligatoire' => 1,
			],
            'date_terminee' => [
				'nom' => "Date terminée",
				'type' => 4,
				'obligatoire' => 1,
			],
			'utilisateur_id' => [
				'nom' => "Utilisateur",
				'type' => 42,
				'type_element_ajax' => 'utilisateur',
				'obligatoire' => 1,
			],
		],
	];