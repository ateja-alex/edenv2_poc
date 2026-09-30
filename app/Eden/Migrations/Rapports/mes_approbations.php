<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Mes approbations',
	'description' => "Liste des actions à approuver",
	'ordre' => 20,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'approbation',

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'approbation',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0","2"],
                    'nom_sql' => 'approbation',
                ],
                [
                    'type_element' => 'approbation',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["#utilisateur_connecte#"],
                    'nom_sql' => 'destinataire_id',
                ],
            ]
          ],
        ],
		
		'colonnes' => [
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Utilisateur',  'valeur' => 'utilisateur_id', 'ordre' => 1),
			array('nom' => 'Element', 'valeur' => '', 'methode' => 'liste_affiche_element', 'ordre' => 2),
		],
		'calculs' => [
		],
		
		// filtres disponibles pour les clients
		'filtres' => [
		],
	],
];