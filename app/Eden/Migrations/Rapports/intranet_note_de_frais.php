<?php

return [

	'categorie' => 'crm',
	'icone' => 'table',
	'titre' => 'Intranet note de frais',
	'description' => "",
	'ordre' => 501,
	'inactif' => 0,
    'intranet' => 1,


	// paramètres de la liste libre
	'liste_libre' => [

		'type_element' => 'note_de_frais',
		'desactiver_creation' => 1,
		'desactiver_actions' => 1,

		'colonnes' => [
			array("nom" => "#", "valeur" => "id", "ordre" => "0"),
			array("nom" => "Date", "valeur" => "date", "ordre" => "1"),
            array("nom" => "Client", "valeur" => "client_id", "ordre" => "2"),
            array("nom" => "Projet", "valeur" => "projet_id", "ordre" => "3"),
            array("nom" => "Acceptée", "valeur" => "accepte", "ordre" => "4"),
            array("nom" => "Montant TTC", "valeur" => "montant_ttc", "ordre" => "5"),
		],
		'calculs' => [],

		'filtres' => [],

		'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'note_de_frais',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["#utilisateur_connecte#"],
                    'nom_sql' => 'utilisateur_id',
                ],
            ]
          ],
        ],

	],
];