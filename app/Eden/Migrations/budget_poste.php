<?php

return [
		'table_libre' => [
			'nom_table' => "Budget: postes",
			'nom_table_sql' => "budget_poste",
			'description' => "",
			'feminin' => "",
			'element' => "poste du budget",
			'type_element' => "budget_poste",
			'element_pluriel' => "postes du budget",
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
			'article' => [
				'nom' => "Article",
				'afficher_sur_formulaire' => 1,
			],
			
		],
	];