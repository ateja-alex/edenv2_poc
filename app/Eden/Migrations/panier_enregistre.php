<?php

return [
		'table_libre' => [
			'nom_table' => "Paniers enregistrés",
			'nom_table_sql' => "panier_enregistre",
			'description' => "",
			'feminin' => "e",
			'element' => "Panier enregistré",
			'type_element' => "panier_enregistre",
			'element_pluriel' => "Paniers enregistrés",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => "client",
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'panier' => [
				'nom' => "Panier",
				'type' => 6,
			],
		],
	];