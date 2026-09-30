<?php

return [
		'table_libre' => [
			'nom_table' => "Comptes emails",
			'nom_table_sql' => "compte_email",
			'description' => "",
			'feminin' => "",
			'element' => "compte email",
			'type_element' => "compte_email",
			'element_pluriel' => "comptes emails",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#adresse_email#',
		],
		'champs_libres' => [
			'token' => [
				'nom' => "Token",
				'lecture_seule' => 1,
			],
			'adresse_email' => [
				'nom' => "Adresse email",
        		'recherche' => 1,
			],
			'alias' => [
				'nom' => "Alias mail",
                'recherche' => 1,
			],
			'mot_de_passe_smtp' => [
				'nom' => "Mot de passe SMTP",
                'format_champ' => "password",
			],
			'nom_expediteur' => [
				'nom' => "Nom de l'expéditeur",
                'recherche' => 1,
			],
			'utilisateur_id' => [
				'nom' => "Utilisateur",
				'obligatoire' => 1,
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
                'recherche' => 1,
			],
			'valide' => [
				'nom' => "Validé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'image_signature' => [
				'nom' => "Image signature",
				'type' => 7,
			],
			'signature'=> [
				'nom' => "Signature",
				'type' => 7,
			],
			'mail_public'=> [
				'nom' => "Compte email public",
				'type' => 20,
				'liste_choix' => 14,
			],
            'configuration_email' => [
                'nom' => 'Configuration email',
                'type' => 42,
                'type_element_ajax' => 'configuration_email',
            ],
            'type_de_compte'=> [
                'nom' => "Type de compte",
                'type' => 20,
                'liste_choix' => 19,
            ],
			'par_defaut'=> [
                'nom' => "Par défaut",
                'type' => 20,
                'liste_choix' => 14,
            ],
			'aliases' => [
				'nom' => "Aliases",
				'type' => 10,
				'type_reference' => 0,
				'format_champ' => "email",
			],
		],
	];
