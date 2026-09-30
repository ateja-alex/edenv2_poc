<?php

return [
		'table_libre' => [
			'nom_table' => "Notifications",
			'nom_table_sql' => "notification",
			'description' => "",
			'feminin' => "e",
			'element' => "notification",
			'type_element' => "notification",
			'element_pluriel' => "notifications",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
			'table_systeme' => 0
		],
		'champs_libres' => [
			'date' => [
				'nom' => "Date",
				'type' => 5,
			],
			'vue' => [
				'nom' => "Vue",
				'type' => 20,
				'liste_choix' => 14,
			],
			'utilisateur_id' => [
				'nom' => "Utilisateur",
				'type' => 42,
				'type_element_ajax' => 'utilisateur',
			],
			'zone' => [
				'nom' => "Zone",
			],
			'contenu_html' => [
				'nom' => "Contenu",
                'type' => 6,
			],
			'icone' => [
				'nom' => "Icone",
			],
			'type_element' => [
				'nom' => "Type element",
			],
			'element_id' => [
				'nom' => "ID element",
				'type' => 2,
			],
			'entite_id' => [
				'nom' => "Entite",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'notification_flux_id' => [
				'nom' => "Flux de notification",
				'type' => 42,
				'type_element_ajax' => 'notification_flux',
			],
			
		],
	];
