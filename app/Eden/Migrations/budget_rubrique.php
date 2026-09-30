<?php

return [
		'table_libre' => [
			'nom_table' => "Budget: rubriques",
			'nom_table_sql' => "budget_rubrique",
			'description' => "",
			'feminin' => "",
			'element' => "rubrique du budget",
			'type_element' => "budget_rubrique",
			'element_pluriel' => "rubriques du budget",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'rubrique_id' => [
				'nom' => "Rubrique",
				'type' => 42,
				'type_element_ajax' => 'budget_rubrique',
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
				'afficher_sur_formulaire' => 1,
			],
			'type_de_rubrique' => [
				'nom' => "Type de rubrique",
				'type' => 20,
				'liste_choix' => 200,
			],
			
		],
	];