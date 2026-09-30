<?php

return [
		'table_libre' => [
			'nom_table' => "Docusign document",
			'nom_table_sql' => "docusign_document",
			'description' => "",
			'feminin' => "",
			'element' => "document",
			'type_element' => "docusign_document",
			'element_pluriel' => "documents",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'enveloppe_id' => [
                'nom' => "Enveloppe",
                'type' => 42,
                'type_element_ajax' => 'docusign_enveloppe'
            ],
            'lien_fichier' => [
                'nom' => "Lien fichier",
                'obligatoire' => "1",
                'type' => 7
            ],
            'fichier' => [
                'nom' => "Fichier",
                'type' => 7
            ],
            'destination_document_signe' => [
                'nom' => "Destination document signé",
                'type' => 6,
                'obligatoire' => "1",
            ],
            'remplacement_fichier' => [
                'nom' => "Remplacement du fichier",
                'type'=> 20,
                'liste_choix' => 14,
                'format_champ' => 'toggle'
            ],
            'nom_fichier' => [
                'nom' => "Nom fichier",
            ],
            'nom_fichier_signe' => [
                'nom' => "Nom fichier signé",
            ],
            'lien_fichier_signe' => [
                'nom' => "Lien fichier signé",
                'type' => 7
            ],
		],
	];