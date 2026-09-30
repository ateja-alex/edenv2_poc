<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => "Factures à payer",
	'description' => "Liste des factures à payer",
	'ordre' => 0,
	'inactif' => 0,
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'facture_achat',
		
		'colonnes' => [
			['nom' => '#', 'valeur' => 'id', 'ordre' => 0],
			['nom' => 'Fournisseur', 'valeur' => 'fournisseur_id', 'ordre' => 1],
			['nom' => 'Date', 'valeur' => 'date', 'ordre' => 2],
			['nom' => 'Date de réglement', 'valeur' => 'date_de_reglement', 'ordre' => 3],
			['nom' => 'HT', 'valeur' => 'montant_document_ht', 'ordre' => 4],
			['nom' => 'Référence document', 'valeur' => 'reference_document', 'ordre' => 5],
		],
		
		// filtres disponibles pour les clients
		'filtres' => [
			['nom_sql' => 'entite_id'],
			['nom_sql' => 'date_de_reglement'],
		],
		
		// filtres appliqués par défaut (non modifiables)
        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'facture_achat',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'valide',
                ],
                [
                    'type_element' => 'facture_achat',
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


