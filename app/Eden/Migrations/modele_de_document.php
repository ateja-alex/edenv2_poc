<?php

return [
		'table_libre' => [
			'nom_table' => "Modèles de document",
			'nom_table_sql' => "modele_de_document",
			'description' => "",
			'feminin' => "",
			'element' => "modèle de document",
			'type_element' => "modele_de_document",
			'element_pluriel' => "Modèles de document",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'affichage_recherche' => '#nom#',
		],
		'champs_libres' => [
            
			'nom' => [
				'nom' => "Nom du modèle",
				'afficher_sur_formulaire' => 1,
            ],
            'type_element' => [
                'nom' => "Type element",
                'type' => 10,
				'type_reference' => 20,
                'liste_choix' => 71,
				'afficher_sur_formulaire' => 1,
            ],
			'nom_vue' => [
				'nom' => "Nom de la vue",
				'afficher_sur_formulaire' => 1,
            ],
			'header' => [
				'nom' => "Header",
                'type' => 6,
            ],
			'css' => [
				'nom' => "CSS",
                'type' => 6,
            ],
			'body' => [
				'nom' => "Body",
                'type' => 6,
            ],
			'recap_footer' => [
				'nom' => "recap_footer",
                'type' => 6,
            ],
			'footer' => [
				'nom' => "Footer",
                'type' => 6,
            ],
			'annexes' => [
				'nom' => "Annexes",
                'type' => 6,
            ],

			'numero_page_x_1' => [
				'nom' => "Numéro page X (1)",
				'nom_sql' => "numero_page_x_1",
				'type' => "2",
				'valeur_defaut' => 525,
			],
			'numero_page_x_2' => [
				'nom' => "Numéro page X (2)",
				'nom_sql' => "numero_page_x_2",
				'type' => "2",
			],
			'numero_page_y_1' => [
				'nom' => "Numéro page Y (1)",
				'nom_sql' => "numero_page_y_1",
				'type' => "2",
				'valeur_defaut' => 800,
			],
			'numero_page_y_2' => [
				'nom' => "Numéro page Y (2)",
				'nom_sql' => "numero_page_y_2",
				'type' => "2",
			],
			'numero_page_chaine_1' => [
				'nom' => "Numéro page chaine (1)",
				'nom_sql' => "numero_page_chaine_1",
				'type' => "0",
				'valeur_defaut' => 'Page {PAGE_NUM} / {PAGE_COUNT}',
			],
			'numero_page_chaine_2' => [
				'nom' => "Numéro page chaine (2)",
				'nom_sql' => "numero_page_chaine_2",
				'type' => "0",
			],
			'numero_page_taille_1' => [
				'nom' => "Numéro page taille (1)",
				'nom_sql' => "numero_page_taille_1",
				'type' => "2",
				'valeur_defaut' => 10,
			],
			'numero_page_taille_2' => [
				'nom' => "Numéro page taille (2)",
				'nom_sql' => "numero_page_taille_2",
				'type' => "2",
			],
            'numero_page_couleur_1' => [
                'nom' => "Numéro page couleur (1)",
                'nom_sql' => "numero_page_couleur_1",
                'type' => "9",
            ],
            'numero_page_couleur_2' => [
                'nom' => "Numéro page couleur (2)",
                'nom_sql' => "numero_page_couleur_2",
                'type' => "9",
            ],
			'type_de_document' => [
				'nom' => "Type de document",
				'nom_sql' => "type_de_document",
				'type' => "20",
				'liste_choix' => "630",
			],
			'type_element_autres' => [
				'nom' => "Type d'élément autres",
				'nom_sql' => "type_element_autres",
				'type' => "21",
				'contenu' => "[{\"type_element\":\"client\",\"valeur\":true}]",
			],
            'ordre' => [
                'nom' => "Ordre ",
                'nom_sql' => "ordre",
                'type' => "2",
            ],
            'condition_affichage' => [
                'nom' => "Condition d'affichage",
            ],
            'nom_pdf_genere' => [
                'nom' => "Nom du pdf généré",
            ],
			'condition_enregistrement_dans_champ' => [
                'nom' => "Condition d'enregistrement dans un champ du modèle",
            ],
			'champ_enregistrement' => [
				'nom' => "Champ d'enregistrement",
			],
			'retirer_cgv_cga' => [
				'nom' => "Retirer les CGV/CGA",
				'nom_sql' => "retirer_cgv_cga",
				'type' => "20",
				'liste_choix' => "14",
			],
			'facturation_electronique_active' => [
				'nom' => "Facturation électronique active",
				'nom_sql' => "facturation_electronique_active",
				'type' => "20",
				'liste_choix' => "14",
				'format_champ' => 'toggle',
			],
			'parametrage_facturation_electronique' => [
				'nom' => "Paramétrage facturation électronique",
				'nom_sql' => "parametrage_facturation_electronique",
				'type' => "6",
			],
			'fond_page_premiere' => [
				'nom' => "Fond de page - Première page",
				'nom_sql' => "fond_page_premiere",
				'type' => "7",
			],
			'fond_page_milieu' => [
				'nom' => "Fond de page - Pages intermédiaires",
				'nom_sql' => "fond_page_milieu",
				'type' => "7",
			],
			'fond_page_derniere' => [
				'nom' => "Fond de page - Dernière page",
				'nom_sql' => "fond_page_derniere",
				'type' => "7",
			],
		],
	];