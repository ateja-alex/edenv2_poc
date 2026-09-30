<?php

return [
		'table_libre' => [
			'nom_table' => "Style ligne document",
			'nom_table_sql' => "style_ligne_document",
			'description' => "",
			'feminin' => "",
			'element' => "Style ligne document",
			'type_element' => "style_ligne_document",
			'element_pluriel' => "Style lignes documents",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'type' => 0,
				'afficher_sur_formulaire' => 1,				
			],
			'gras' => [
				'nom' => "Gras",
				'type' => 20,
                'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,				
			],
			'italique' => [
				'nom' => "Italique",
				'type' => 20,
                'liste_choix' => 14,            
				'afficher_sur_formulaire' => 1,				
            ],
            'taille_police' => [
				'nom' => "Taille de police",
				'type' => 0,
				'afficher_sur_formulaire' => 1,				
            ],
            'couleur' => [
				'nom' => "Couleur",
				'type' => 9,
				'afficher_sur_formulaire' => 1,				
            ],
		],
	];