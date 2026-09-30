<?php

return [
		'table_libre' => [
			'nom_table' => "Synchronisation service élément historique",
			'nom_table_sql' => "synchronisation_service_element_historique",
			'description' => "",
			'feminin' => "",
			'element' => "synchronisation service élément historique",
			'type_element' => "synchronisation_service_element_historique",
			'element_pluriel' => "synchronisation service élément historiques",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'synchronisation_service_element_id' => [
				'nom' => "Synchronisation service élément",
                'type' => 42,
                'type_element_ajax' => 'synchronisation_service_element',
			],
			'type_element' => [
				'nom' => "Type element",
			],
			'element_id' => [
				'nom' => "Element id"
			],
			'type_evenement' => [
				'nom' => "Type d'événement",
				'type' => 20,
				'liste_choix' => 728
			],
			'valeurs_transmises' => [
				'nom' => "Valeurs transmises",
				'type' => 6,
			],
			'valeur_recus' => [
				'nom' => "Valeurs reçues",
				'type' => 6,
			],
		],
	];