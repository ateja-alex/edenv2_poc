<?php

return [
		'table_libre' => [
			'nom_table' => "Ordres dans kanban",
			'nom_table_sql' => "ordre_dans_kanban",
			'description' => "Ordre dans kanban",
			'feminin' => "",
			'element' => "ordre",
			'type_element' => "ordre_dans_kanban",
			'element_pluriel' => "ordres",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'id_rapport' => [
				'nom' => "ID rapport",
			],
			'element_id' => [
				'nom' => "ID élément",
				'type' => 2,
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],
			
		],
	];
