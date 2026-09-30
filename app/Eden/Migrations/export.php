<?php

return [
		'table_libre' => [
			'nom_table' => "Exports",
			'nom_table_sql' => "export",
			'description' => "Liste des exports",
			'feminin' => "e",
			'element' => "export",
			'type_element' => "export",
			'element_pluriel' => "exports",
			'fiche' => 0,

			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'type_element_createur' => [
                'nom' => "Type élément créateur",
                'type' => 21,
                'contenu' => '[{"type_element":"utilisateur","valeur":true},{"type_element":"client","valeur":true},{"type_element":"contact","valeur":true}]',
            ],
            'element_id_createur' => [
                'nom' => "Élément ID créateur",
                'type' => 22,
                'contenu' => 'type_element_createur'
            ],
			'type_export' => [
				'nom' => "Type export",
            ],
            'id_liste' => [
				'nom' => "Id liste",
            ],
            'parametres' => [
                'nom' => 'Paramètres'
            ],
			'termine' => [
				'nom' => "Terminé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'page' => [
				'nom' => "page",
            ],
            'fichier' => [
                'nom' => 'fichier'
            ],
            'nombre_en_cours' => [
                'nom' => 'Nombre en cours',
                'type' => 3,
            ],
            'nombre_elements' => [
                'nom' => 'Nombre elements',
                'type' => 3,
            ],
            'export_en_cours' => [
                'nom' => 'Export en cours',
                'type' => 20,
                'liste_choix' => 14,
            ],
		],
	];
