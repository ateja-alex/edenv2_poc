<?php

return [
		'table_libre' => [
			'nom_table' => "Mouvements de lots",
			'nom_table_sql' => "lot_mouvement",
			'description' => "",
			'feminin' => "",
			'element' => "mouvement de lot",
			'type_element' => "lot_mouvement",
			'element_pluriel' => "mouvements de lots",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'ERP',
		],
		'champs_libres' => [
			'lot_id' => [
				'nom' => "Lot",
				'type' => 42,
				'type_element_ajax' => 'lot',
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
			],
			'quantite' => [
				'nom' => "Quantité",
				'type' => 3,
			],
			'type_element' => [
				'nom' => "Type element",
			],
			'element_id' => [
				'nom' => "ID element",
				'type' => 2,
			],
		],
	];