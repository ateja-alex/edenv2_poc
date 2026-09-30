<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Lignes des devis à passer en commande',
	'description' => "Lignes des devis à passer en commande",
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
                    'type_element' => 'devis_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'accepte',
                ],
                [
                    'type_element' => 'devis_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0","1","3"],
                    'nom_sql' => 'transforme',
                ],
                [
                    'type_element' => 'devis_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["1"],
                    'nom_sql' => 'valide',
                ],
            ]
          ],
        ],

        'type_element' => 'devis_vente_lignes',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array("nom" => "Devis", "valeur" => "document_id","lien_vers_autre_element" => "document_id",  'ordre' => 1),
			array('nom' => 'Client',  'valeur' => 'client_id_ligne',"lien_vers_autre_element" => "client_id_ligne", 'ordre' => 2),
			array('nom' => 'Article',  'valeur' => 'article_id',"lien_vers_autre_element" => "article_id", 'ordre' => 3),
			array('nom' => 'Quantité',  'valeur' => 'quantite', 'ordre' => 4),
			array('nom' => 'Reste à passer en commande',  'valeur' => 'transforme_reliquat', 'ordre' => 5),
		],
		'calculs' => [
			
		],
		'filtres' => [
			
		],
		
	],
];