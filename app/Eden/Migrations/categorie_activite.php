<?php

return [
		'table_libre' => [
			'nom_table' => "Catégorie activité",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "catégorie activité",
			'type_element' => "categorie_activite",
			'element_pluriel' => "catégories activité",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
				'recherche' => 1,
			],
            'ordre' => [
                'nom' => "Ordre",
                'type' => 2,
            ],
			'utilisateurs_id' => [
				'nom' => "Affectations",
				'type' => 10,
				'type_reference' => 42,
				'type_element_ajax' => 'utilisateur'
			],
		],
	];