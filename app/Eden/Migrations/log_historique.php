<?php

return [
		'table_libre' => [
			'nom_table' => "Logs historique",
			'nom_table_sql' => "log_historique",
			'description' => "",
			'feminin' => "",
			'element' => "log historique",
			'type_element' => "log_historique",
			'element_pluriel' => "logs historiques",
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
			'utilisateur_id' => [
				'nom' => "Utilisateur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			'url' => [
				'nom' => "URL",
			],
            'nom_page' => [
                'nom' => "Nom de la page",
				'type' => 6
            ],
		],
	];