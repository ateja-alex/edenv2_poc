<?php

return [
		'table_libre' => [
			'nom_table' => "Zones",
			'nom_table_sql' => "zone_transporteur_cp",
			'description' => "",
			'feminin' => "",
			'element' => "zone transporteur CP",
			'type_element' => "zone_transporteur_cp",
			'element_pluriel' => "zones transporteur CP",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#departement#',
		],
		'champs_libres' => [
			'pays_id' => [
				'nom' => "Pays",
				'obligatoire' => 1,
				'type' => 20,
				'liste_choix' => 28
			],
			'departement' => [
				'nom' => "Département"
			],
			'zone_id' => [
				'nom' => "Zone",
				'type' => 42,
				'type_element_ajax' => 'zone_transporteur'
			],
		],
	];