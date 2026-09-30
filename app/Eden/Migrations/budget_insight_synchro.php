<?php

return [
		'table_libre' => [
			'nom_table' => "Synchro Budget Insight",
			'nom_table_sql' => "budget_insight_synchro",
			'description' => "",
			'feminin' => "",
			'element' => "synchro",
			'type_element' => "budget_insight_synchro",
			'element_pluriel' => "synchros",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'token' => [
				'nom' => "Token",
				'obligatoire' => 1,
			],
		],
	];
