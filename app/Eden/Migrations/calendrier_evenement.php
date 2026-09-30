<?php

return [
		'table_libre' => [
			'nom_table' => "Evenements sur les plannings",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "événement",
			'type_element' => "calendrier_evenement",
			'element_pluriel' => "événements",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [

			'type_element' => [
				'nom' => "Type élément",
			],
			'element_id' => [
				'nom' => "ID élément",
				'type' => 2,
			],
			'date_debut' => [
				'nom' => "Date de début",
				'type' => 5,
				'afficher_sur_formulaire' => 1,
			],
			'date_fin' => [
				'nom' => "Date de fin",
				'type' => 5,
				'afficher_sur_formulaire' => 1,
			],
			'nom' => [
				'nom' => "Nom",
				'afficher_sur_formulaire' => 1,
			],
			'synchro_element_id' => [
				'nom' => "Element ID",
				'type' => 2,
			],
			'synchro_type_element' => [
				'nom' => "Type Element",
			],
		],
	];