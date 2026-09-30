<?php

return [

	'categorie' => 'crm',
	'icone' => 'table',
	'titre' => 'Intranet employé demande de congés',
	'description' => "",
	'ordre' => 500,
	'inactif' => 0,
    'intranet' => 1,


	// paramètres de la liste libre
	'liste_libre' => [

		'type_element' => 'employe_demande_conge',
        'desactiver_creation' => 1,
		'desactiver_actions' => 1,

		'colonnes' => [
			array("nom" => "#", "valeur" => "id", "ordre" => "0"),
			array("nom" => "Date de demande", "valeur" => "date_de_demande", "ordre" => "1"),
            array("nom" => "Date de début", "valeur" => "date_de_debut", "ordre" => "2"),
            array("nom" => "Date de fin", "valeur" => "date_de_fin", "ordre" => "3"),
            array("nom" => "Type", "valeur" => "raison", "ordre" => "4"),
            array("nom" => "Acceptée", "valeur" => "statut", "ordre" => "5"),
		],
		'calculs' => [],

		'filtres' => [],

        'filtres_appliques' => [
          [
            'operateur' => 0,
            'blocs' => [],
            'filtres' => [
                [
                    'type_element' => 'employe_demande_conge',
                    'element_id' => null,
                    'champ_liaison' => null,
                    'valeurs' => ["#utilisateur_connecte#"],
                    'nom_sql' => 'employe_id',
                ],
            ]
          ],
        ],

	],
];