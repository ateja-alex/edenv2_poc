<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Devis en attente',
	'description' => "Liste des devis en attente de décision",
	'ordre' => 11,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'devis_vente',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Client',  'valeur' => 'client_id', 'ordre' => 1),
			array('nom' => 'Date',  'valeur' => 'date', 'ordre' => 2),
			array('nom' => 'Montant HT',  'valeur' => 'montant_document_ht', 'ordre' => 3),
		],
		'calculs' => [
			
			array('nom_sql' => 'montant_document_ht', 'type_calcul' => 'SUM', 'nom' => 'Total HT', 'split' => '', 'unite' => '€', ),
		],
		
		// filtres disponibles pour les clients
		'filtres' => [],
		
		// filtres appliqués par défaut (non modifiables)
        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'devis_vente',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'accepte',
                ],
            ]
          ],
        ],
		
	],
];