<?php

return [
	'table_libre'   => [
		'nom_table'                   => "Caution",
		'nom_table_sql'               => "caution",
		'description'                 => "",
		'feminin'                     => "",
		'element'                     => "caution",
		'type_element'                => "caution",
		'element_pluriel'             => "cautions",
		'fiche'                       => 0,
		'disponible_recherche_rapide' => 0,
		'creation_rapide'             => 0,
		'module'                      => 'Gestion commerciale',
	],
	'champs_libres' => [
		'client_id' => [
			'nom'               => "Client",
			'type'              => 42,
			'type_element_ajax' => "client",
		],
		'date' => [
			'nom'  => "Date",
			'type' => 4,
		],
		'montant' => [
			'nom'  => "Montant",
			'type' => 3,
		],
		'adresse' => [
            'nom' => "Adresse",
        ],
        'debit_carte_bleue' => [
            'nom'         => "Payée par carte bleue",
            'type'        => 20,
            'liste_choix' => 14,
        ],
        'entite_id' => [
            'nom'         => "Entité",
            'type' => 42,
            'type_element_ajax' => 'entite',
        ],
	],
];
