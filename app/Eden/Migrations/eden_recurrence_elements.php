<?php

return [
		'table_libre' => [
			'nom_table' => "Abonnements",
			'nom_table_sql' => "eden_recurrence_elements",
			'description' => "",
			'feminin' => "",
			'element' => "abonnement",
			'type_element' => "eden_recurrence_elements",
			'element_pluriel' => "abonnements",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'mode_recurrence' => [
				'nom' => "Mode de récurrence",
				'type' => 20,
				'liste_choix' => 60,
			],
			'type_element' => [
				'nom' => "Type element",
			],
			'rdi_date_generation' => [
				'nom' => "Date de génération",
			],
            'rdi_mois_generation' => [
                'nom' => "Mois de génération",
            ],
			'rdi_id_modele' => [
				'nom' => "Modèle",
				'type' => 20,
				'liste_choix' => 61,
			],
			'rdi_prochaine_occurence' => [
				'nom' => "Prochaine occurence",
				'type' => 4,
			],
			'inactif' => [
				'nom' => "Terminée",
				'type' => 20,
				'liste_choix' => 14,
			],
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => 'client',
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
		    'rdd_date_generation' => [
				'nom' => "Date génération",
				'type' => 2,
		    ],
		    'rdd_mois_generation' => [
				'nom' => "Génération de mois",
				'type' => 2,
		    ],
			'rdd_occurences' => [
				'nom' => "Nombre d'occurences",
				'type' => 2,
		    ],
			'rdd_periodicite' => [
				'nom' => "Périodicité",
				'type' => 2,
		    ],
            'delai_generation' => [
                'nom' =>  "Délai en jours de la génération du document",
                'type' => 2,
            ],
            'generation_progressive' => [
                'nom' =>  "Génération progressive",
                'type' => 20,
                'liste_choix' => 14,
            ]
		],
	];