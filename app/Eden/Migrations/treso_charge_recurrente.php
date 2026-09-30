<?php

return [
		'table_libre' => [
			'nom_table' => "Charges recurrentes",
			'nom_table_sql' => "treso_charge_recurrente",
			'description' => "",
			'feminin' => "",
			'element' => "Charge recurrente",
			'type_element' => "treso_charge_recurrente",
			'element_pluriel' => "Charges recurrentes",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'charge' => [
				'nom' => "Charge recurrente",
			],
			'montant' => [
				'nom' => "Montant",
				'type' => 3,
			],
			'charge_mensuelle' => [
				'nom' => "Charge mensuelle",
				'type' => 2,
			],
			
			'mois_selectionnes' => [
				'nom' => "Mois selectionnes",
				'type' => 6,
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],
			'entite_id' => [
				'nom' => "entite_id",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
		],
	];