<?php

return [
		'table_libre' => [
			'nom_table' => "Intégration email comptes emails",
			'nom_table_sql' => "integration_email_comptes_emails",
			'description' => "",
			'feminin' => "",
			'element' => "intégration email compte email",
			'type_element' => "integration_email_comptes_emails",
			'element_pluriel' => "intégrations emails comptes emails",
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'cle_locale' => [
				'nom' => "Intégration email",
                'type' => 42,
                'type_element_ajax' => 'integration_email',
                'obligatoire' => 1
			],
            'valeur' => [
                'nom' => "Compte email",
                'type' => 42,
                'type_element_ajax' => 'compte_email',
                'obligatoire' => 1
            ],
            'boite_mail' => [
                'nom' => "Boîte mail",
            ],
            'boite_deplacement_mail' => [
                'nom' => "Boîte déplacement mail",
            ],
		],
	];