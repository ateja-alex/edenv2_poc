<?php

return [
		'table_libre' => [
			'nom_table' => "Intégration email correspondance",
			'nom_table_sql' => "integration_email_correspondance",
			'description' => "",
			'feminin' => "",
			'element' => "intégration email correspondance",
			'type_element' => "integration_email_correspondance",
			'element_pluriel' => "intégrations emails correspondances",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#titre#',
		],
		'champs_libres' => [
			'integration_email_id' => [
				'nom' => "Intégration email",
                'type' => 42,
                'type_element_ajax' => 'integration_email'
			],
            'mail_reponse' => [
                'nom' => 'Réponse au mail',
                'type' => 20,
                'liste_choix' => 14
            ],
            'champ_eden' => [
                'nom' => "Champ eden",
            ],
            'valeur' => [
                'nom' => "Valeur",
            ],
            'champ_correspondance_type_42' => [
                'nom' => 'Champ de correspondance pour les types 42'
            ]
		],
	];