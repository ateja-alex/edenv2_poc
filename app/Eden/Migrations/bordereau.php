<?php

return [
		'table_libre' => [
			'nom_table' => "Bordereau",
			'nom_table_sql' => "bordereau",
			'description' => "",
			'feminin' => "",
			'element' => "bordereau",
			'type_element' => "bordereau",
			'element_pluriel' => "bordereaux",
			'fiche' => 1,

			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
			'editable_client' => 1,
			'categorie' => 'element_primaire',
			'icone_fontawesome' => 'fa-credit-card',
		],
		'champs_libres' => [
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'obligatoire' => 1,
                'afficher_sur_formulaire' => 1,
			],
			'montant' => [
				'nom' => "Montant",
				'type' => 3,
			],
			'banque' => [
				'nom' => "Banque",
                'type' => 20,
                'liste_choix' => 4,
                'afficher_sur_formulaire' => 1,
			],
			'statut' => [
				'nom' => "Statut",
                'type' => 20,
                'liste_choix' => 141,
                'afficher_sur_formulaire' => 1,
			],
            'nombre_de_cheques' => [
                'nom' => 'Nombre de paiements',
                'type' =>  2
            ],
            'numero' => [
                'nom' => 'Numéro de bordereau',
                'afficher_sur_formulaire' => 1,
            ],
            'entite_id' => [
                'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
                'afficher_sur_formulaire' => 1,
            ],
            'mode_de_paiement' => [
				'type_element' => "bordereau",
				'nom_sql' => "mode_de_paiement",
				'type' => 20,
				'liste_choix' => 7,
			],
	    ],
	];