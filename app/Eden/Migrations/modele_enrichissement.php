<?php

return [
		'table_libre' => [
			'nom_table' => "Modèle d'enrichissement",
			'nom_table_sql' => "modele_enrichissement",
			'description' => "",
			'feminin' => "",
			'element' => "modèle d'enrichissement",
			'type_element' => "modele_enrichissement",
			'element_pluriel' => "modèles d'enrichissements",
			'fiche' => 0,
			'parametre' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
            'type_element' => [
                'nom' => "Type d'élément",
            ],
		],
	];