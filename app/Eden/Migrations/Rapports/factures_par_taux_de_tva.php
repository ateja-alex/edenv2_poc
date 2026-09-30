<?php

return [

	'categorie' => 'compta',
	'icone' => 'table',
	'titre' => 'TVA par facture',
	'description' => 'Affiche les bases HT pour chaque facture et chaque taux de TVA',
	'ordre' => 1,
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
			array('nom' => 'TVA 0%',  'valeur' => '', 'ordre' => 5, 'methode' => 'montant_tva_0'),
			array('nom' => 'TVA 5.5%',  'valeur' => '', 'ordre' => 6, 'methode' => 'montant_tva_5_5'),
			array('nom' => 'TVA 10%',  'valeur' => '', 'ordre' => 7, 'methode' => 'montant_tva_10'),
			array('nom' => 'TVA 20%',  'valeur' => '', 'ordre' => 8, 'methode' => 'montant_tva_20'),
		],
		'calculs' => [],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'date']],

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
                    'nom_sql' => 'annulee_par_avoir',
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