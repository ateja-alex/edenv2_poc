<?php

return [
		'table_libre' => [
			'nom_table' => "Transporteurs",
			'nom_table_sql' => "transporteur",
			'description' => "",
			'feminin' => "",
			'element' => "transporteur",
			'type_element' => "transporteur",
			'element_pluriel' => "transporteurs",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 1,
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