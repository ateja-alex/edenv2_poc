<?php

return [
		'table_libre' => [
			'nom_table' => "Notifications element",
			'nom_table_sql' => "notification_element",
			'description' => "",
			'feminin' => "e",
			'element' => "notification élément",
			'type_element' => "notification_element",
			'element_pluriel' => "notifications élément",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'type_element' => [
				'nom' => "Type élément",
			],
			'element_id' => [
				'nom' => "ID élément",
				'type' => 2,
			],
			'utilisateur_id' => [
				'nom' => "Utilisateur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			
		],
	];