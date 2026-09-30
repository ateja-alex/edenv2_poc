<?php

return [
		'table_libre' => [
			'nom_table' => "Echange Ticket Client",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "echange",
			'type_element' => "ticket_client_echange",
			'element_pluriel' => "echanges",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'suivi_recette' => [
				'nom' => "Bug",
				'type' => 42,
				'obligatoire' => 1,
				'type_element_ajax' => "ticket_client",
			],
			'message' => [
				'nom' => "Message",
				'type' => 6,
			],
			'pieces_jointes' => [
				'nom' => "Pièces jointes",
				'type' => 15,
			],
			'type_message' => [
				'nom' => "Type de message",
				'type' => 20,
				'liste_choix' => 580,
                'valeur_defaut' => 0,

			],
			'auteur_extranet' => [
				'nom' => "Auteur extranet",
			],
			'mail_id' => [
				'nom' => "Mail id",
			],
			'mail_html' => [
				'nom' => "Mail format HTML",
				'type' => 20,
				'liste_choix' => 14,
			],
            'date' => [
                'nom' => "Date de réception",
                'type' => 5
			],
		],
	];
