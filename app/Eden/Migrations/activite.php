<?php

return [
		'table_libre' => [
			'nom_table' => "Activité",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "activité",
			'type_element' => "activite",
			'element_pluriel' => "activités",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion de projets',
            'affichage_dans_liste' => '#activite#',
		],
		'champs_libres' => [
			'activite' => [
				'nom' => "Activité",
				'obligatoire' => 1,
				'recherche' => 1,
			],
			'vendu' => [
				'nom' => "Vendu",
				'type' => 3,
			],
			'previsionnel' => [
				'nom' => "Prévisionnel",
				'type' => 3,
			],
			'type_element' => [
                'nom' => 'Type élement',
                'type' => 21,
                'contenu' => '[{"type_element":"projet","valeur":true}]',
            ],
            'element_id' => [
                'nom' => "Element ID",
                'type' => 22,
                'contenu' => 'type_element',
            ],
			'categorie_activite_id' => [
				'nom' => "Catégorie activité",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'categorie_activite'
			],
			'realise' => [
				'nom' => "Réalisé",
				'type' => 3,
			],
			'utilisateurs_id' => [
				'nom' => "Affectations",
				'type' => 10,
				'type_reference' => 42,
				'type_element_ajax' => 'utilisateur'
			],
			'prioritaire' => [
				'nom' => "Prioritaire",
				'type' => 20,
				'liste_choix' => 14,
			],
            'article_id' => [
                'nom' => "Article",
                'type' => 42,
                'type_element_ajax' => 'article',
            ],
            'tarif' => [
                'nom' => "Prix de vente unitaire",
                'type' => 3,
            ],
            'remise_pourcentage' => [
                'nom' => "Pourcentage de remise",
                'type' => 3,
            ]
		],
	];