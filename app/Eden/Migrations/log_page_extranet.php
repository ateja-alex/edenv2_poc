<?php

return [
		'table_libre' => [
			'nom_table' => "Logs page extranet",
			'nom_table_sql' => "log_page_extranet",
			'description' => "",
			'feminin' => "",
			'element' => "log",
			'type_element' => "log_page_extranet",
			'element_pluriel' => "logs",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'ERP',
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'date' => [
				'nom' => "Date",
				'type' => 5,
			],
			'utilisateur_extranet_id' => [
				'nom' => "Utilisateur_extranet",
				'type' => 42,
				'type_element_ajax' => 'utilisateur_extranet',
			],
		],
	];