<?php

return [
		'table_libre' => [
			'nom_table' => "Intégration email",
			'nom_table_sql' => "integration_email",
			'description' => "",
			'feminin' => "",
			'element' => "intégration email",
			'type_element' => "integration_email",
			'element_pluriel' => "intégrations emails",
			'fiche' => 1,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
            'nom' => [
                'nom' => "Nom",
                'obligatoire' => 1
            ],
            'type_element' => [
                'nom' => "Type élément",
            ],
            'comptes_emails' => [
                'nom' => 'Comptes emails',
                'type' => 10,
                'type_element_ajax' => 'compte_email'
            ],
            'table_gestion_reponse' => [
                'nom' => "Table de gestion des réponses"
            ],
            'champ_gestion_reponse' => [
                'nom' => "Champ de gestion des réponses"
            ],
            'balise_gestion_reponse' => [
                'nom' => "Balise à détecter dans le mail en tant qu'identifiant EDEN"
            ]
		],
	];