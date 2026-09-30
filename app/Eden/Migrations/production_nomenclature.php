<?php

return [
		'table_libre' => [
			'nom_table' => "Production assemblages",
			'nom_table_sql' => "production_nomenclature",
			'description' => "",
			'feminin' => "",
			'element' => "assemblage",
			'type_element' => "production_nomenclature",
			'element_pluriel' => "assemblages",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'article' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
				'filtres' => [
					'article' => array (
					array (
						'operateur' => 0,
						'exclu' => 0,
						'blocs' => array (),
						'filtres' => 
						array (
						array (
							'type_element' => 'article',
							'champ_liaison' => NULL,
							'valeurs' => array (3),
							'nom_sql' => 'type_article',
							'operateur' => 0,
						),
						),
					),
					), 
				],
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'valeur_defaut' => '#aujourdhui#',
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'quantite' => [
				'nom' => "Quantité",
				'type' => 2,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'entrepot' => [
				'nom' => "Entrepot",
				'type' => 20,
				'liste_choix' => 8,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
            'reserve' => [
                'nom' => "Réservé",
                'type' => 20,
                'liste_choix' => 14,
                'obligatoire' => 1,
                'afficher_sur_formulaire' => 1,
            ],
            'pdf' => [
                'nom' => "PDF",
            ],
		],
	];