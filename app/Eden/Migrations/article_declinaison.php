<?php

return [
		'table_libre' => [
			'nom_table' => "article_declinaison",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "article declinaison",
			'type_element' => "article_declinaison",
			'element_pluriel' => "articles declinaisons",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'reference' => [
				'nom' => "Référence",
			],
			'designation' => [
				'nom' => "Désignation",
			],
			'code_article' => [
				'nom' => "Code article",
			],
			'tarif' => [
				'nom' => "Tarif",
				'type' => 3,
			],
			'stock' => [
				'nom' => "Stock",
				'type' => 3,
			],
			'poids' => [
				'nom' => "Poids",
				'type' => 3,
			],
			'image' => [
				'nom' => "image",
				'type' => 7,
			],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
			],
			'article_declinaison_id' => [
				'nom' => "Article de la déclinaison",
				'type' => 42,
				'type_element_ajax' => 'article',
			],
			'cout_de_revient' => [
				'nom' => "Coût de revient",
				'type' => 3,
			],
			'famille_declinaison_1' => [
				'nom' => "famille declinaison 1",
                'type' => 20,
				'liste_choix' => 59,

			],
			'famille_declinaison_2' => [
				'nom' => "famille declinaison 2",
                'type' => 20,
				'liste_choix' => 59,

			],
			'famille_declinaison_3' => [
				'nom' => "famille declinaison 3",
                'type' => 20,
				'liste_choix' => 59,
            ],
			'famille_declinaison_4' => [
				'nom' => "famille declinaison 4",
                'type' => 20,
				'liste_choix' => 59,
            ],
			'famille_declinaison_5' => [
				'nom' => "famille declinaison 5",
                'type' => 20,
				'liste_choix' => 59,
            ],
			'valeur_declinaison_1' => [
				'nom' => "valeur declinaison 1",
			],
			'valeur_declinaison_2' => [
				'nom' => "valeur declinaison 2",
			],
			'valeur_declinaison_3' => [
				'nom' => "valeur declinaison 3",
			],
			'valeur_declinaison_4' => [
				'nom' => "valeur declinaison 4",
            ],
			'valeur_declinaison_5' => [
				'nom' => "valeur declinaison 5",
            ],
	
            
		],
	];