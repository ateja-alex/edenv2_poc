<?php

return [
		'table_libre' => [
			'nom_table' => "Synchronisation mail",
			'nom_table_sql' => "synchro_mail",
			'description' => "",
			'feminin' => "",
			'element' => "synchro_mail",
			'type_element' => "synchro_mail",
			'element_pluriel' => "synchro_mails",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
            'statut' => [
				'nom' => "Statut",
			],
			'utilisateur_id' => [

				'nom' => "Utilisateur ID",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
            'configuration_email' => [

				'nom' => "Configuration email",
				'type' => 42,
				'type_element_ajax' => 'configuration_email',
			],
            'type' => [
                'nom' => 'Type de compte à synchronisation',
                'type' => 20,
                'liste_choix' => 602,
            ],
            'email_synchro' => [
                'nom' => 'Email de synchronisation'
            ],
            'dossier_synchroniser' => [
                'nom' => 'Dossier de synchronisation'
            ],
		],
	];