<?php

return [
		'table_libre' => [
			'nom_table' => "Erreurs",
			'nom_table_sql' => "erreur",
			'description' => "Liste des erreurs",
			'feminin' => "e",
			'element' => "erreur",
			'type_element' => "erreur",
			'element_pluriel' => "erreurs",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'titre' => [
				'nom' => "Titre",
				'lecture_seule' => 1,
			],
			'session' => [
				'nom' => "Session",
				'lecture_seule' => 1,
				'type' => 6,
			],
			'request' => [
				'nom' => "Request",
				'lecture_seule' => 1,
				'type' => 6,
			],
			'message_erreur' => [
				'nom' => "Message d'erreur",
				'lecture_seule' => 1,
			],
			'app_env' => [
				'nom' => "App Env",
				'lecture_seule' => 1,
			],
			'url' => [
				'nom' => "URL",
				'lecture_seule' => 1,
			],
			'origine' => [
				'nom' => "Origine",
				'type' => 20,
				'liste_choix' => 41,
			]
		],
	];
