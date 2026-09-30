<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => "Relances à effectuer",
	'description' => "Liste des relances factures à effectuer",
	'ordre' => 0,
	'inactif' => 0,
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'relance_recouvrement',
		
		'colonnes' => [
			
			array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
			array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 1),
			array('nom' => 'Facture', 'valeur' => 'facture_vente_id', 'ordre' => 2),
			array('nom' => 'Date', 'valeur' => 'date', 'ordre' => 3),
			array('nom' => 'Type relance', 'valeur' => 'type_relance', 'ordre' => 4),
		],
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'entite_id']],

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'relance_recouvrement',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'a_faire',
                ],
            ]
          ],
        ],
		
	],
];


