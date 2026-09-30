<?php

return [
		'table_libre' => [
			'nom_table' => "Revenus récurrents",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "Revenu récurrent",
			'type_element' => "treso_revenu_recurrent",
			'element_pluriel' => "Revenus récurrents",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Tréso',
		],
		'champs_libres' => [
			'source_revenu' => [
				'nom' => "Source de revenu",
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],
			'montants_mensuels' => [
				'nom' => "Montants mensuels",
				'type' => 6,
			],
			'entite_id' => [
				'nom' => "entite_id",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
		],
	];