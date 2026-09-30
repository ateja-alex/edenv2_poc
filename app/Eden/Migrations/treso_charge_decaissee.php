<?php

return [
		'table_libre' => [
			'nom_table' => "Charges decaissees",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "Charge décaissée",
			'type_element' => "treso_charge_decaissee",
			'element_pluriel' => "Charges décaissées",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Tréso',
		],
		'champs_libres' => [
			'id_charge_recurrente' => [
				'nom' => "Id Charge recurrente",
				'type' => 2,
			],
			'date' => [
				'nom' => "date",
				'type' => 5,
			],
			'entite_id' => [
				'nom' => "entite_id",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
		],
	];