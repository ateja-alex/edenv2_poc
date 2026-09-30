<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => "Commandes non facturées",
	'description' => "Afficher les commandes qui n'ont pas encore été facturées",
	'ordre' => 0,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'commande_vente',
		
		'colonnes' => [
			
			array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 1),
			array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 2),
			array('nom' => 'Montant HT', 'valeur' => 'montant_document_ht', 'ordre' => 3),
		],
		'calculs' => [],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'date']],
		
		// filtres appliqués par défaut (non modifiables)
        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'commande_vente',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'transforme_en_facture',
                ],
            ]
          ],
        ],
		
	],
];