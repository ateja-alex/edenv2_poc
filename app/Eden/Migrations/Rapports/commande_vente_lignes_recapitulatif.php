<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Lignes commande vente ligne récapitulatif',
	'description' => "Récapitulatif des lignes de commande vente",
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
            ]
          ],
        ],
		'type_element' => 'commande_vente_lignes',
		
		'colonnes' => [
			
			array('nom' => '#',  'valeur' => 'id', 'ordre' => 0),
			array("nom" => "Devis", "valeur" => "document_id","lien_vers_autre_element" => "document_id",  'ordre' => 1),
			array('nom' => 'Client',  'valeur' => 'client_id_ligne',"lien_vers_autre_element" => "client_id_ligne", 'ordre' => 2),
			array('nom' => 'Article',  'valeur' => 'article_id',"lien_vers_autre_element" => "article_id", 'ordre' => 3),
			array('nom' => 'Quantité',  'valeur' => 'quantite', 'ordre' => 4),
			array('nom' => 'PU',  'valeur' => 'tarif', 'ordre' => 5),
			array('nom' => 'Reste à commander',  'valeur' => 'transforme_reliquat_commande_fournisseur', 'ordre' => 6),
			array('nom' => 'Reste à recevoir',  'valeur' => 'transforme_reliquat_reception_fournisseur', 'ordre' => 7),
			array('nom' => 'Reste à livrer',  'valeur' => 'transforme_reliquat', 'ordre' => 8),
		],
		'calculs' => [
			
		],
		'filtres' => [
			
		],
		
	],
];