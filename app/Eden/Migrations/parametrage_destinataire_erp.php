<?php

return [
		'table_libre' => [
			'nom_table' => "Paramètrage destinataire erp",
			'nom_table_sql' => "parametrage_destinataire_erp",
			'description' => "",
			'feminin' => "",
			'element' => "paramètrage destinataire erp",
			'type_element' => "parametrage_destinataire_erp",
			'element_pluriel' => "paramètrages destinataire erp",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'notification_manuelle_id' => [
                'nom' => 'Notification manuelle',
                'type' => 42,
                'type_element_ajax' => 'notification_manuelle'
            ],
            'utilisateur_id'=>[
                'nom' => 'Utilisateur',
                'type' => 42,
                'type_element_ajax' => 'utilisateur'
            ],
            'lien_champ'=>[
                'nom' => 'Lien champ',
                'type' => 6
            ],
            'parametrage_existant'=>[
                'nom' => 'Paramétrage existant',
            ],
            'nom_simplifie'=>[
                'nom' => 'Nom simplifié',
            ],
		],
	];