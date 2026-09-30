<?php

return [
		'table_libre' => [
			'nom_table' => "Import en cours",
			'nom_table_sql' => "import_en_cours",
			'description' => "",
			'feminin' => "",
			'element' => "import en cours",
			'type_element' => "import_en_cours",
			'element_pluriel' => "imports en cours",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'import_sur_mesure' => [
				'nom' => "Import sur mesure",
                'type' => 42,
                'type_element_ajax' => 'import_sur_mesure',
			],
            'date' => [
				'nom' => "Date",
                'type' => 5,
			],
            'statut' => [
                'nom' => "Statut",
                'type' => 20,
				'liste_choix' => 570,
            ],
            'nom_fichier' => [
                'nom' => 'Nom du fichier'
            ],
            'chemin_acces' => [
                'nom' => "Chemin d'accés du fichier"
            ],
            'nombres_a_importer' => [
                'nom' => "Nombre d'éléments à importer",
                'type' => 2,
            ],
            'nombres_importes' => [
                'nom' => "Nombre d'éléments importés",
                'type' => 2,
            ],
            'combinaisons_cles' => [
                'nom' => "Combinaisons de clés",
				'type' => 6,
            ],
            'erreurs' => [
                'nom' => "Erreurs",
				'type' => 6,
            ],
            'types_notifications' => [
                'nom' => "Type de notification à recevoir après l'import",
                'type' => 10,
				'type_reference' => 20,
                'liste_choix' => 73,
            ],
		],
	];