<?php

return [
		'table_libre' => [
			'nom_table' => "Suivi recette Easy Développement",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "bug",
			'type_element' => "suivi_recette_easydev",
			'element_pluriel' => "bugs",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
            'affichage_dans_liste' => '#titre#',
			'affichage_dans_kanban' => '#titre#',
		],
		'champs_libres' => [
			'titre' => [
				'nom' => "Titre",
				'afficher_sur_formulaire' => 1,
                'recherche' => 1,
			],
			'description' => [
				'nom' => "Description",
				'type' => 6,
				'afficher_sur_formulaire' => 1,
				'format_champ' => 'wysiwyg',
                'recherche' => 1,
			],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'liste_choix' => 65,
				'afficher_sur_formulaire' => 1,
				'lecture_seule' => 1,
                'obligatoire' => 1,
			],
			'priorite' => [
				'nom' => "Priorité",
				'type' => 20,
				'liste_choix' => 145,
				'afficher_sur_formulaire' => 1,
			],
			'evolution' => [	
                'nom' => "Evolution",	
                'type' => 20,	
				'liste_choix' => 14,	
			],
			'ordre_kanban' => [	
                'nom' => "Ordre",	
                'type' => 2,	
			],
			'date_de_cloture' => [
				'type' => "4",
				'nom' => "Date de clôture",
				'lecture_seule' => "1",
			],
			'date' => [
				'type' => "4",
				'nom' => "Date de création",
				'lecture_seule' => "1",
			],
			'images' => [
				'type' => "15",
				'nom' => "Images",
			],
            'url' => [
                'nom' => "URL",
                'afficher_sur_formulaire' => 1,
            ],
		],
	];