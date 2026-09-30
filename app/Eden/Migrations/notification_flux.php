<?php

return [
		'table_libre' => [
			'nom_table' => "Notifications flux",
			'nom_table_sql' => "notification_flux",
			'description' => "",
			'feminin' => "e",
			'element' => "notification flux",
			'type_element' => "notification_flux",
			'element_pluriel' => "notifications flux",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
			],	
			'index' => [
				'nom' => "Index",
			],
			'transport' => [
				'nom' => "Notification",
				'type' => 20,
				'liste_choix' => 73,
			],	
			'template' => [
				'nom' => "Template",
				'type' => 6,
			],		
			'sujet' => [
				'nom' => "Sujet",
			],		
			'couleur_tag' => [
				'nom' => "Couleur tag",
			],		
			'couleur_tag_police' => [
				'nom' => "Couleur police tag",
			],		
		],
	];