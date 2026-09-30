<?php

return [
		'table_libre' => [
			'nom_table' => "Budget: valeurs",
			'nom_table_sql' => "budget_valeur",
			'description' => "",
			'feminin' => "",
			'element' => "valeur du budget",
			'type_element' => "budget_valeur",
			'element_pluriel' => "valeurs du budget",
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
			'valeur' => [
				'nom' => "Valeur",
				'type' => 3,
			],
			'id_budget_poste' => [
				'nom' => "Poste",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'budget_poste',
			],
			
		],
	];