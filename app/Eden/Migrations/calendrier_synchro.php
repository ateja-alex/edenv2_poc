<?php

return [
		'table_libre' => [
			'nom_table' => "Calendrier synchronisation",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "synchro",
			'type_element' => "calendrier_synchro",
			'element_pluriel' => "synchros",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [

			'nom' => [
				'nom' 						=> "Nom",
				'afficher_sur_formulaire' 	=> 1,
			],

			'champ_date_debut' => [
				'nom' 						=> "Champ date de début",
				'afficher_sur_formulaire' 	=> 1,
			],

			'champ_date_fin' => [
				'nom' 						=> "Champ date de fin",
				'afficher_sur_formulaire' 	=> 1,
			],

			'type_element_synchro' => [
				'nom' 						=> "Type élément Synchro",
				'afficher_sur_formulaire' 	=> 1,
			],
			
			'type_element_parent' => [
				'nom' 						=> "Type élément parent",
				'afficher_sur_formulaire' 	=> 1,
			],
			
			'champ_element_parent' => [
				'nom' 						=> "Champ élément parent",
				'afficher_sur_formulaire' 	=> 1,
			],
		],
	];