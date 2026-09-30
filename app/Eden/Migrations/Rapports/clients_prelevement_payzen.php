<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Prélèvements Payzen des clients',
	'description' => "Liste des clients en attente de prélèvement Payzen",
	'ordre' => 10,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'client',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Client',  'valeur' => 'denomination', 'ordre' => 1),
			array('nom' => 'Solde dû',  'valeur' => '', 'methode' => 'colonne_solde_du', 'ordre' => 2),
			array('nom' => 'Action',  'valeur' => '', 'methode' => 'colonne_payzen_prelever', 'ordre' => 3),
		],
		'calculs' => [],
		
		// filtres disponibles pour les clients
		'filtres' => [],

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'client',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => [
                        'variable' => 'vide',
                        'texte' => null,
                    ],
                    'nom_sql' => 'portefeuille_payzen_prelevement',
                ],
            ]
          ],
        ],
		
	],
];