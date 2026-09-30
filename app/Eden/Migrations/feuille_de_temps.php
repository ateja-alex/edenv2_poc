<?php

return [
		'table_libre' => [
			'nom_table' => "Feuilles de temps",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "feuille de temps",
			'type_element' => "feuille_de_temps",
			'element_pluriel' => "feuilles de temps",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion de projet',
		],
		'champs_libres' => [
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'obligatoire' => 1,
                'valeur_defaut' => "#aujourdhui#",
			],
            'cout' => [
                'nom' => "Coût",
                'type' => 3,
            ],
            'type_element' => [
                'nom' => 'Type élement',
                'type' => 21,
                'contenu' => '[{"type_element":"projet","valeur":true}]',
                'valeur_defaut' => "projet",
            ],
            'element_id' => [
                'nom' => "Element ID",
                'type' => 22,
                'contenu' => 'type_element',
            ],
			'activite_id' => [
				'nom' => "Activité",
				'type' => 42,
				'type_element_ajax' => "activite",
			],
			'categorie_id' => [
				'nom' => "Catégorie",
				'type' => 42,
				'type_element_ajax' => "categorie_activite",
			],
			'duree' => [
				'nom' => "Durée",
				'type' => 3,
				'obligatoire' => 1,
			],
            'duree_jours' => [
                'nom' => "Temps passé (jours)",
                'type' => 3
            ],
			'utilisateur_id' => [
				'nom' => "Affectation",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
                'valeur_defaut' => "#utilisateur_connecte#",
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
            'tache' => [
                'nom' => "Tache",
            ],
            'type_element_transforme' => [
                'nom' => "Type document transformé",
                'type' => 21,
                'contenu' => '[{"type_element":"facture_vente_lignes","valeur":true}, {"type_element":"bl_vente_lignes","valeur":true}]',

            ],
            'element_id_transforme' => [
                'nom' => "Element ID",
                'type' => 22,
                'contenu' => 'type_element',
            ],
		],
	];