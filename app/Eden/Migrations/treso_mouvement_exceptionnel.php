<?php

return [
		'table_libre' => [
			'nom_table' => "Mouvements Exceptionnels",
			'nom_table_sql' => "treso_mouvement_exxceptionnel",
			'description' => "",
			'feminin' => "",
			'element' => "Mouvement exceptionnel",
			'type_element' => "treso_mouvement_exceptionnel",
			'element_pluriel' => "Mouvements exceptionnels",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
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