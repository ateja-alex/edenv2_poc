<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Articles nomenclature',
	'description' => "Articles de type nomenclature",
	'ordre' => 10,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'article',
        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'article',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'type_article',
                ],
            ]
          ],
        ],
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Désignation',  'valeur' => 'designation', 'ordre' => 1),
			array('nom' => 'Tarif',  'valeur' => 'tarif', 'ordre' => 2, 'retour_a_la_ligne_impossible' => 1),
			array('nom' => 'Famille',  'valeur' => 'famille_id', 'ordre' => 3),
		],
		
	],
];