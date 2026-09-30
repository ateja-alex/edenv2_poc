<?php

return [
		'table_libre' => [
			'nom_table' => "Synchro Google Drive & Sharepoint",
			'nom_table_sql' => "bibliotheque_synchro_elements",
			'description' => "",
			'feminin' => "",
			'element' => "synchro",
			'type_element' => "bibliotheque_synchro_elements",
			'element_pluriel' => "synchro",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'table_systeme' => 1,
            'parametre' => 0,
		],
		'champs_libres' => [
			'type_element' => [
				'nom' => "Type élément",
			],
			'element_id' => [
				'nom' => "ID élément",
				'type' => 2,
			],
            'type_document' => [
                'nom' => "Type document",
            ],
            'document_id' => [
                'nom' => "ID document",
            ],
			'id_dossier' => [
				'nom' => "ID Dossier",
			],
            'stockage_externe' => [
                'nom' => 'Stockage externe',
                'type' => 20,
                'liste_choix' => 591
            ],
            'derniere_synchro' => [
                'nom' => 'Dernière synchronisation de l\'élément',
                'type' => 5
            ]
		],
	];