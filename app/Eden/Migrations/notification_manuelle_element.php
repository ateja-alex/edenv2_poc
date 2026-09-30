<?php

return [
		'table_libre' => [
			'nom_table' => "Notification manuelle element",
			'nom_table_sql' => "notification_manuelle_element",
			'description' => "",
			'feminin' => "e",
			'element' => "notification manuelle element",
			'type_element' => "notification_manuelle_element",
			'element_pluriel' => "notifications manuelles elements",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
		],
		'champs_libres' => [
			'element_id' => [
				'nom' => "Element",
                'type' => 2,
			],
            'notification_manuelle_id' => [
				'nom' => "Notification manuelle",
                'type' => 42,
                'type_element_ajax' => 'notification_manuelle',
			],
			'envoyee' => [
                'nom' => 'Envoyée',
                'type' => 20,
                'liste_choix' => 14,
            ],
            'erreur_envoi' => [
                'nom' => "Erreur lors de l'envoi",
                'type' => 6,
            ],
            'erreur_nombre_essai' => [
                'nom' => "Nombre d'essais en erreur",
                'type' => 2,
                'valeur_defaut' => '0',
            ],
            'erreur_prochain_essai' => [
                'nom' => "Date du prochain essai",
                'type' => 5,
            ],
		],
	];