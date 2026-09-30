<?php

return [
		'table_libre' => [
			'nom_table' => "Intérêt des leads pour les entités",
			'nom_table_sql' => "interet_lead_entite",
			'description' => "",
			'feminin' => "",
			'element' => "intérêt",
			'type_element' => "interet_lead_entite",
			'element_pluriel' => "intérêts",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'lead_id' => [
				'nom' => "Lead",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'lead',
			],
			'entite_id' => [
				'nom' => "Entité",
				'obligatoire' => 1,
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'interet' => [
				'nom' => "Intérêt",
				'type' => 20,
				'liste_choix' => 45,
			],
			
			
		],
	];