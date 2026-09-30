<?php

return [
		'table_libre' => [
			'nom_table' => "Feuille de temps commentaire",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "feuille de temps commentaire",
			'type_element' => "feuille_de_temps_commentaire",
			'element_pluriel' => "feuilles de temps commentaires",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion de projet',
		],
		'champs_libres' => [
            'commentaire' => [
                'nom' => "Commentaire",
                'type' => 6,
                'obligatoire' => 1,
            ],
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'obligatoire' => 1,
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
            'mode_affichage' => [
                'nom' => "Mode d'affichage",
                'type' => 20,
                'liste_choix' => 631
            ],
		],
	];