<?php

return [
		'table_libre' => [
			'nom_table' => "Email reçus",
			'nom_table_sql' => "email_recus",
			'description' => "",
			'feminin' => "",
			'element' => "email_recus",
			'type_element' => "email_recus",
			'element_pluriel' => "emails_recus",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'sujet' => [
				'nom' => "Sujet",
			],
			'date' => [
				'nom' => "Date",
				'type' => 5,				
			],
			'to' => [
				'nom' => "to",
			],
			'from' => [
				'nom' => "From",
			],
			'bcc' => [
				'nom' => "Bcc",
			],
			'cc' => [
				'nom' => "Cc",
			],
			'pieces_jointes' => [
				'nom' => "Pièces jointes",
			],
			'id_mail' => [
				'nom' => "Id mail",
			],
			'texte' => [
				'nom' => "Texte",
                'type' => 6,
			],
            'texte_html' => [
				'nom' => "Texte HTML",
                'type' => 6,
			],
			'utilisateur_id' => [
				'nom' => "Utilisateur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
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
			'projet_id' => [
				'nom' => "Projet",
				'type' => 42,
				'type_element_ajax' => 'projet',
			],
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'type_element_ajax' => 'fournisseur',
			],
			'public' => [
				'nom' => "Public",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
				'modifier_en_masse' => 1,
			],
			'synchro_mail_id' => [
				'nom' => "Compte mail",
				'type' => 20,
				'liste_choix' => 69,
			],
			'traite' => [
				'nom' => "Traité",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
				'modifier_en_masse' => 1,
			],
			'en_charge' => [
				'nom' => "En charge",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
				'afficher_sur_formulaire' => 1,
				'modifier_en_masse' => 1,
			],
            'internet_message_id' => [
				'nom' => "Internet message id",
			],
            'pieces_jointes_charges' => [
                'nom' => 'Pièces jointes chargés',
                'type' => 20,
                'liste_choix' => 14
            ]
		],
	];