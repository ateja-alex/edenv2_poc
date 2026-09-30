<?php

return [
		'table_libre' => [
			'nom_table' => "Devises",
			'nom_table_sql' => "devise",
			'description' => "",
			'feminin' => "",
			'element' => "devise",
			'type_element' => "devise",
			'element_pluriel' => "devises",
			'fiche' => 0,
			'parametre' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
                'afficher_sur_formulaire' => 1,
            ],
            'code' => [
                'nom' => "Code",
                'afficher_sur_formulaire' => 1,
            ],
            'disponible' => [
                'nom' => "Disponible",
                'type' => 20,
                'liste_choix' => 14,
                'afficher_sur_formulaire' => 1,
            ],
		],
	];