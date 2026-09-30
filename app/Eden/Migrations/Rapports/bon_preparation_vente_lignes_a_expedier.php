<?php

return [

	'categorie' => 'gestion_commerciale',
	'icone' => 'table',
	'titre' => 'Articles à expédier',
	'description' => "Articles à expédier",
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
                    'type_element' => 'bon_preparation_vente_lignes',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0","1"],
                    'nom_sql' => 'transforme',
                ],
            ]
          ],
        ],

		'type_element' => 'bon_preparation_vente_lignes',
		
		'colonnes' => [
			
			array("nom" => "#", "valeur" => "id", "ordre" => "0", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
			array("nom" => "Bon de préparation", "valeur" => "document_id", "ordre" => "2", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
			array("nom" => "Client", "valeur" => "client_id_ligne", "ordre" => "3", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
			array("nom" => "Article", "valeur" => "article_id", "ordre" => "4", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
			array("nom" => "Quantité", "valeur" => "quantite", "ordre" => "5", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
			array("nom" => "Reste à livrer", "valeur" => "transforme_reliquat", "ordre" => "6", "lien_vers_element" => "", "methode" => "", "lien_vers_autre_element" => null, "tri_desactive" => "", "type" => "standard", "champ" => "", "responsive" => "", "caracteres_max" => "", "standard" => "", "retour_a_la_ligne_impossible" => "", "couleur_colonne" => "",  ),
		],
		'calculs' => [
			
		],
		'filtres' => [
			
		],
		
	],
];