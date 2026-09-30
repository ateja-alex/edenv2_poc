<?php

return [
	'table_libre'   => [
		'nom_table'                   => "Equipe",
		'nom_table_sql'               => "",
		'description'                 => "",
		'feminin'                     => "e",
		'element'                     => "equipe",
		'type_element'                => "equipe",
		'element_pluriel'             => "equipes",
		'fiche'                       => 0,
		
		'disponible_recherche_rapide' => 0,
		'creation_rapide'             => 0,
		'parametre'             	  => 1,
		'module'                      => 'Gestion commerciale',
	],
	'champs_libres' => [
		'nom' => [
			'nom'         => "Nom",
			'type'        => 0,
			'obligatoire' => 1,
		],
		'couleur_fond' => [
			'nom'  => "Couleur de fond",
			'type' => 9,
		],
		'couleur_police' => [
			'nom'  => "Couleur de police",
			'type' => 9,
		],
        'type_tache_rdv_par_defaut' => [
            'nom' => "Type de tache de rendez-vous par défaut",
            'type' => 20,
            'liste_choix' => 504,
        ],
        'type_rendez_vous_disponible' => [
            'nom' => "Type de rendez-vous disponible",
            'type' => 10,
			'type_reference' => 20,
            'liste_choix' => 504,
        ],
	],
];