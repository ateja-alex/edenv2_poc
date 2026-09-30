<?php

return [
		'table_libre' => [
			'nom_table' => "Saisie des temps éléments sélectionnés",
			'nom_table_sql' => "sdt_date_elements_selectionnes",
			'description' => "",
			'feminin' => "",
			'element' => "date avec éléments sélétionnés",
			'type_element' => "sdt_date_elements_selectionnes",
			'element_pluriel' => "dates avec éléments sélétionnés",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
		    'date_debut' => [
                'nom' => "Date début",
                'type' => 4
            ],
            'date_fin' => [
                'nom' => "Date fin",
                'type' => 4
            ],
            'utilisateur_id' => [
                'nom' => "Utilisateur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur'
            ],
            'mode_affichage' => [
                'nom' => "Mode d'affichage",
                'type' => 20,
                'liste_choix' => 631
            ],
			'type_element' => [
				'nom' => "Type élément",
			],
			'elements_ids' => [
				'nom' => "Elements ids",
                'type' => 6,
			],
		],
	];
