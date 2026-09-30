<?php

return [
		'table_libre' => [
			'nom_table' => "Import sur mesure",
			'nom_table_sql' => "import_sur_mesure",
			'description' => "",
			'feminin' => "",
			'element' => "import sur mesure",
			'type_element' => "import_sur_mesure",
			'element_pluriel' => "imports sur mesure",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#titre#',
		],
		'champs_libres' => [
            'titre' => [
				'nom' => "Titre",
                'obligatoire' => 1,
                'recherche' => 1,
			],
            'type_element' => [
				'nom' => "Type élement",
                'obligatoire' => 1,
			],
			'tables_jointes' => [
				'nom' => "Tables jointes",
				'type' => 6,
			],
            'champs' => [
				'nom' => "Champs",
				'type' => 6,
			],
            'valeurs_par_defaut' => [
                'nom' => "Valeurs par défaut",
				'type' => 6,
            ],
            'complet' => [
                'nom' => 'Complet',
                'type' => 20,
                'liste_choix' => 14,
            ],
            'ordre_lancement_import' => [
                'nom' => "Ordre de lancement de l'import",
				'type' => 6,
            ],
		],
	];