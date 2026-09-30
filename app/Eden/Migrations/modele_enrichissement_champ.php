<?php

return [
		'table_libre' => [
			'nom_table' => "Modèle d'enrichissement champ",
			'nom_table_sql' => "modele_enrichissement_champ",
			'description' => "",
			'feminin' => "",
			'element' => "modèle d'enrichissement champ",
			'type_element' => "modele_enrichissement_champ",
			'element_pluriel' => "modèles d'enrichissement champs",
			'fiche' => 0,
			'parametre' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'modele_enrichissement_id' => [
				'nom' => "Modèle d'enrichissement",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'modele_enrichissement',
			],
            'type' => [
                'nom' => "Type",
				'type' => 20,
				'liste_choix' => 716,
            ],
			'nom_sql' => [
				'nom' => "Nom SQL",
				'obligatoire' => 1,
			],
		],
	];