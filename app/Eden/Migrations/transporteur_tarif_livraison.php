<?php

return [
		'table_libre' => [
			'nom_table' => "Zone transporteurs tarifs livraison",
			'nom_table_sql' => "transporteur_tarif_livraison",
			'description' => "",
			'feminin' => "",
			'element' => "tarif",
			'type_element' => "transporteur_tarif_livraison",
			'element_pluriel' => "tarifs",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#transporteur_id#',
		],
		'champs_libres' => [
			'transporteur_id' => [
				'nom' => "Transporteur",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'transporteur'
			],
			'zone_id' => [
				'nom' => "Zone",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'zone_transporteur'
			],
			'tarif' => [
				'nom' => "Tarif",
				'type' => 3,
			],
			'poids_min' => [
				'nom' => "Poids Min",
				'type' => 3,
			],
			'poids_max' => [
				'nom' => "Poids Max",
				'type' => 3,
			],
		],
	];