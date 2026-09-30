<?php

return [
		'table_libre' => [
			'nom_table' => "Notification manuelle",
			'nom_table_sql' => "notification_manuelle",
			'description' => "",
			'feminin' => "e",
			'element' => "notification manuelle",
			'type_element' => "notification_manuelle",
			'element_pluriel' => "notifications manuelles",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
            'affichage_dans_liste' => '#titre#',
		],
		'champs_libres' => [
            'titre' => [
				'nom' => "Titre",
                'obligatoire' => 1,
                'recherche' => 1,
			],
            'type_notification' => [
				'nom' => "Type notification",
				'type' => 20,
				'liste_choix' => 541,
                'obligatoire' => 1,
			],
			'type_element_id' => [
				'nom' => "Type element",
				'type' => 20,
				'liste_choix' => 71,
                'obligatoire' => 1,
			],
            'message_notification' => [
				'nom' => "Message de notification",
                'type' => 6,
                'format_champ' => 'wysiwyg',
			],
             'modele_email' => [
				'nom' => "Modele de l'email",
                'type' => 42,
                'type_element_ajax' => 'modele_email',
                'format_champ' => 'select'
			],
			'sujet' => [
				'nom' => "Sujet",
			],
            'contenu_email' => [
				'nom' => "Contenu de l'email",
                'type' => 6,
                'format_champ' => 'wysiwyg',
			],
            'compte_email_id' => [
				'nom' => "Compte email",
                'type' => 42,
                'type_element_ajax' => 'compte_email',
			],
			'utilisateur_expediteur_id' => [
				'nom' => "Utilisateur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			'type_expediteur'=>[
                'nom' => 'Type expéditeur',
            ],
			'lien_champ_expediteur'=>[
                'nom' => 'Lien champ',
                'type' => 6
            ],
            "condition" => [
				'nom' => "Condition",
                'type' => 20,
				'liste_choix' => 539,
                'obligatoire' => 1,
			],
            'condition_sql' => [
				'nom' => "Condition sql",
                'type' => 6,
                'aide' => "Les conditions inscrites se situeront après le 'WHERE inactif = null'. La condition SQL sera alors précédé de 'AND'",
			],
		],
	];