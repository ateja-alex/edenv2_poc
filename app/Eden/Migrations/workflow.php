<?php

return [
		'table_libre' => [
			'nom_table' => "Workflows",
			'nom_table_sql' => "workflow",
			'description' => "",
			'feminin' => "",
			'element' => "workflow",
			'type_element' => "workflow",
			'element_pluriel' => "workflows",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
		
			'nom' => [
			
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			'type_element' => [
			
				'nom' => "Element",
				'obligatoire' => 1,
				// 'type' => 20,
				// 'liste_choix' => 71,
			],
			'type_action' => [
			
				'nom' => "Action",
				'obligatoire' => 1,
				'type' => 20,
				'liste_choix' => 70,
			],
			'trigger' => [
			
				'nom' => "Trigger",
				'obligatoire' => 1,
				'type' => 20,
				'liste_choix' => 72,
			],
			'condition_avant' => [
			
				'nom' => "Conditions avant",
				'type' => 6,
			],
			'condition_apres' => [
			
				'nom' => "Conditions après",
				'type' => 6,
			],
			'parametrage' => [
			
				'nom' => "Paramétrage",
				'type' => 6,
			],
		],
	];