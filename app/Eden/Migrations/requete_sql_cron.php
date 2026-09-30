<?php

return [
		'table_libre' => [
			'nom_table' => "Requête sql cron",
			'nom_table_sql' => "requete_sql_cron",
			'description' => "",
			'feminin' => "",
			'element' => "requete_sql_cron",
			'type_element' => "requete_sql_cron",
			'element_pluriel' => "requetes_sql_cron",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
		    'titre' => [
                'nom' => "Titre",
            ],
			'requete' => [
			
				'nom' => "Requête",
                'type' => 6,
                'obligatoire' => 1,
			],
			'valeur_interval' => [
			
				'nom' => "Valeur interval",
                'type' => 2,
				'obligatoire' => 1,
			],
			'unite_interval' => [
			
				'nom' => "Unite interval",
				'obligatoire' => 1,
			],
			'derniere_execution' => [
			
				'nom' => "Derniere_execution",
				'type' => 5,
			],
			'dernier_envoi_mail' => [
			
				'nom' => "Dernier envoi de mail",
				'type' => 5,
			],
			'duree_derniere_execution' => [
			
				'nom' => "Durée de la dernière exécution",
				'type' => 3,
			],
		],
	];