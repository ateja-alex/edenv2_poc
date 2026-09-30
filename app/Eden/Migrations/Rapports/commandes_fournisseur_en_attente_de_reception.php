<?php

return [

	'categorie' => 'activite_operationnelle',
	'icone' => 'table',
	'titre' => 'Commandes en attente de réception',
	'description' => "Liste des commandes fournisseurs en attente de réception",
	'ordre' => 11,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'commande_achat',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Fournisseur',  'valeur' => 'fournisseur_id', 'ordre' => 1),
			array('nom' => 'Date',  'valeur' => 'date', 'ordre' => 2),
			array('nom' => 'Date de réception prévue',  'valeur' => 'date_de_reception', 'ordre' => 3),
		],
		'calculs' => [],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'date_de_reception']],
		
		// filtres appliqués par défaut (non modifiables)
        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'commande_achat',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'expedie',
                ],
                [
                    'type_element' => 'commande_achat',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'valide',
                ],
            ]
          ],
        ],
		
	],
];