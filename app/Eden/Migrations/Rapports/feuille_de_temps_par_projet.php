<?php

return [

	'categorie' => 'gestion_projet',
	'icone' => 'table',
	'titre' => 'Feuille de temps par projet',
	'description' => "Les feuilles de temps par projet",
	'ordre' => 5,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'feuille_de_temps',
		
		'colonnes' => [
			
			array('nom' => 'Utilisateur', 'valeur' => 'utilisateur_id', 'ordre' => 0),
			array('nom' => 'Durée', 'valeur' => 'duree', 'ordre' => 1),
			array('nom' => 'Element id', 'valeur' => 'element_id', 'ordre' => 2),
			array('nom' => 'Coût', 'valeur' => 'cout', 'ordre' => 3),
		],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'utilisateur_id']],

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'feuille_de_temps',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["projet"],
                    'nom_sql' => 'type_element',
                ],
            ]
          ],
        ],
		
	],
];