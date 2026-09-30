<?php

return [
		'table_libre' => [
			'nom_table' => "Zone transporteurs",
			'nom_table_sql' => "zone_transporteur",
			'description' => "",
			'feminin' => "e",
			'element' => "zone",
			'type_element' => "zone_transporteur",
			'element_pluriel' => "zones transporteur",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
		],
	];