<?php

return [
		'table_libre' => [
			'nom_table' => "Objectfis de vente par famille",
			'nom_table_sql' => "objectif_vente_par_famille",
			'description' => "",
			'feminin' => "",
			'element' => "objectif",
			'type_element' => "objectif_vente_par_famille",
			'element_pluriel' => "objectifs",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'obligatoire' => 1,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
				'obligatoire' => 1,
			],
			'famille_id' => [
				'nom' => "Famille",
				'type' => 42,
				'type_element_ajax' => 'famille',
				'obligatoire' => 1,
			],
			'objectif' => [
				'nom' => "Objectif",
				'type' => 2,
				'obligatoire' => 1,
			],
		],
	];