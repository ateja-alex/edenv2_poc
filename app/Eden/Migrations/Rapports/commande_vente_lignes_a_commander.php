<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Articles à commander chez les fournisseurs',
	'description' => "Articles à commander chez les fournisseurs",
	'ordre' => 10,
	'inactif' => 0,
	
	// paramètres de la liste libre
	'liste_libre' => [
		'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'commande_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0"],
                    'nom_sql' => 'document_annule',
                ],
                [
                    'type_element' => 'commande_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0","1"],
                    'nom_sql' => 'transforme',
                ],
                [
                    'type_element' => 'commande_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0","1"],
                    'nom_sql' => 'transforme_fournisseur',
                ],
                [
                    'type_element' => 'commande_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'valide',
                ],
            ]
          ],
        ],
		'type_element' => 'commande_vente_lignes',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array("nom" => "Commande", "valeur" => "document_id","lien_vers_autre_element" => "document_id",  'ordre' => 1),
			array('nom' => 'Client',  'valeur' => 'client_id_ligne',"lien_vers_autre_element" => "client_id_ligne", 'ordre' => 2),
			array('nom' => 'Article',  'valeur' => 'article_id',"lien_vers_autre_element" => "article_id", 'ordre' => 3),
			array('nom' => 'Quantité',  'valeur' => 'quantite', 'ordre' => 4),
			array('nom' => 'Reste à commander',  'valeur' => 'transforme_reliquat_commande_fournisseur', 'ordre' => 5),
			array("nom" => 'Reste à livrer',  'valeur' => 'transforme_reliquat', 'ordre' => 6,   ),
		],
		'calculs' => [
			
		],
		'filtres' => [
			
		],
		
	],
];