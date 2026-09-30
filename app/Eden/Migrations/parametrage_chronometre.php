<?php

return [
		'table_libre' => [
			'nom_table' => "Paramétrage du chronométre",
			'nom_table_sql' => "parametrage_chronometre",
			'description' => "",
			'feminin' => "",
			'element' => "parametrage chronometre",
			'type_element' => "parametrage_chronometre",
			'element_pluriel' => "parametrages chronometre",

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'fiche' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'type_element' => [
				'nom' => "Type élément",
                'obligatoire' => 1,
			],
            'champ_libre_correspondance_temps' => [
				'nom' => "Champ libre de correspondance pour le temps",
                'obligatoire' => 1,
			],
            'type_saisie' => [
                'nom' => "Type de saisie",
                'type' => 20,
                'liste_choix' => 700
            ],
		],
	];