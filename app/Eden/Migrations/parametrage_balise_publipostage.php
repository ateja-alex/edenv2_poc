<?php

return [
		'table_libre' => [
			'nom_table' => "Paramètrage balise publipostage",
			'nom_table_sql' => "parametrage_balise_publipostage",
			'description' => "",
			'feminin' => "",
			'element' => "paramètrage balise",
			'type_element' => "parametrage_balise_publipostage",
			'element_pluriel' => "paramètrages balise publipostage",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'type_element_id'=>[
                'nom' => 'Type élément',
                'type' => 20,
                'liste_choix' => 71,
            ],
            'nom'=>[
                'nom' => 'Nom',
				'obligatoire' => 1
            ],
            'valeur'=>[
                'nom' => 'Valeur',
                'type' => 6
            ]
		],
	];