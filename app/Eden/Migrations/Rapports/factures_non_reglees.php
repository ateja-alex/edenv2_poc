<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Factures non réglées',
	'description' => "Liste des factures en attente de règlement",
	'ordre' => 10,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'facture_vente',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Client',  'valeur' => 'client_id', 'ordre' => 1),
			array('nom' => 'Date',  'valeur' => 'date', 'ordre' => 2),
			array('nom' => 'Solde TTC',  'valeur' => 'solde_document_ttc', 'ordre' => 3),
		],
		'calculs' => [
			
			array('nom_sql' => 'solde_document_ttc', 'type_calcul' => 'SUM', 'nom' => 'Encours TTC', 'split' => '', 'unite' => '€', ),
			array('nom_sql' => 'solde_document_ttc', 'type_calcul' => 'SUM', 'nom' => 'Encours TTC / client', 'split' => 'client_id', 'unite' => '€', ),
			array('nom_sql' => 'solde_document_ttc', 'type_calcul' => 'SUM', 'nom' => 'Encours TTC / responsable commercial', 'split' => 'responsable_commercial_id', 'unite' => '€', ),
		],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'responsable_commercial_id']],

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
                ]
            ]
          ],
        ],
		
	],
];