<?php

return [

	'categorie' 	=> 'gestion_commerciale',
	'icone' 		=> 'table',
	'titre' 		=> 'Recouvrement',
	'description' 	=> "Liste des factures et acomptes en attente de paiement",
	'ordre' 		=> 2,
	'inactif' 		=> 0,

	// paramètres de la liste libre
	'liste_libre' => [
		
		'type_element' 		=> 'union_facture_vente_acompte_vente',

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'union_facture_vente_acompte_vente',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["0","2"],
                    'nom_sql' => 'regle',
                ]
            ]
          ],
        ],
		
		'colonnes' => [

			array(
					"nom" 					=> "#", 
					"valeur" 				=> "id",
					"ordre" 				=> 1,
					"type" 					=> "standard",
				),

			array(
				"nom" 						=> "Client",
				"valeur" 					=> "#client_id#<br> #client_id.telephone#<br>#client_id.adresse_email#",
				"ordre" 					=> 2,
				"lien_vers_element" 		=> 1,
				"lien_vers_autre_element" 	=> "client_id",
				"type" 						=> "concatenation",
			),

			array(
				"nom" 						=> "Référence",
				"valeur" 					=> "reference_document",
				"ordre" 					=> 3,
				"lien_vers_element" 		=> 1,
			),

			array(
				"nom" 						=> "Objet",
				"valeur" 					=> "objet",
				"ordre" 					=> 4,
			),

			array(
				"nom" 						=> "Date",
				"valeur" 					=> "date",
				"ordre"					 	=> 5,
			),
			
			array(
				"nom" 						=> "Montant TTC",
				"valeur" 					=> "montant_document_ttc",
				"ordre" 					=> 5,
				"alignement_colonne" 		=> "right",
			),
			
			array(
				"nom" 						=> "Solde TTC",
				"valeur" 					=> "solde_document_ttc",
				"ordre" 					=> 6,
				"alignement_colonne" 		=> "right",
			),
		],

		'calculs' => [
			array(
				"nom_sql" 		=> "montant_document_ttc",
				"type_calcul" 	=> "SUM",
				"nom" 			=> "Montant",
				"split" 		=> "",
				"unite" 		=> "€",
			),
			array(
				"nom_sql" 		=> "solde_document_ttc",
				"type_calcul" 	=> "SUM",
				"nom" 			=> "Solde TTC",
				"split" 		=> "",
				"unite" 		=> "€",
			),
		],
		
		'filtres' => [
			array("nom_sql" => "date"),
			array("nom_sql" => "client_id"),
		],
	],
];