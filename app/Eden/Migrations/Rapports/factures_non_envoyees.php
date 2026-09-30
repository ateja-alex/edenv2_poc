<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Factures non envoyées',
	'description' => "Liste des factures non envoyées",
	'ordre' => 10,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' => 'facture_vente',
		
		'colonnes' => [
			
			array('nom' => 'Client', 'valeur' => 'client_id', 'ordre' => 0),
            array('nom' => 'Référence',  'valeur' => 'reference_document', 'ordre' => 1),
			array('nom' => 'Objet',  'valeur' => 'objet', 'ordre' => 2),
			array('nom' => 'HT',  'valeur' => 'montant_document_ht', 'ordre' => 3),

            array('nom' => 'TTC',  'valeur' => 'montant_document_ttc', 'ordre' => 4),
			array('nom' => 'Origine','valeur' => 'origine', 'ordre' => 5),
		],
	
		
		// filtres disponibles pour les clients
		'filtres' => [['nom_sql' => 'date']],
		
		// filtres appliqués par défaut (non modifiables)
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
                    'nom_sql' => 'envoye_par_mail',
                ]
            ]
          ],
        ],
		
	],
];