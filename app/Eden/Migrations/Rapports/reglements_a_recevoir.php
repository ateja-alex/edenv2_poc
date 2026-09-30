<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Règlements à recevoir',
	'description' => "Liste des règlements à recevoir, avec possibilité de saisir des règlements",
	'ordre' => 10,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'facture_vente',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Client',  'valeur' => 'client_id', 'ordre' => 1),
			array('nom' => 'Date de règlement',  'valeur' => 'date_de_reglement', 'ordre' => 2),
			array('nom' => 'Montant TTC',  'valeur' => 'montant_document_ttc', 'ordre' => 3),
			array('nom' => 'Solde TTC',  'valeur' => 'solde_document_ttc', 'ordre' => 4),
			array('nom' => 'Régler',  'valeur' => '', 'ordre' => 5, 'methode' => 'colonne_reglement_a_recevoir'),
		],
		'calculs' => [
			
			array('nom_sql' => 'solde_document_ttc', 'type_calcul' => 'SUM', 'nom' => 'Encours TTC', 'split' => '', 'unite' => '€', ),
		],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'entite_id']],
        
        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'facture_vente',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'regle',
                ],
                [
                    'type_element' => 'facture_vente',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'annulee_par_avoir',
                ],
                [
                    'type_element' => 'facture_vente',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => [
                        "debut" => null,
                        "fin" => null,
                        "variable" => "jusqu_a_aujourdhui",
                    ],
                    'nom_sql' => 'date_de_reglement',
                ],
                [
                    'type_element' => 'facture_vente',
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