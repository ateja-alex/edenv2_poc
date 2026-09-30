<?php

return [
		'table_libre' => [
			'nom_table' => "Valeur des marges par nature par modèle",
			'nom_table_sql' => "nature_article_modele_marge",
			'description' => "",
			'feminin' => "",
			'element' => "valeur marges par nature",
			'type_element' => "nature_article_modele_marge",
			'element_pluriel' => "valeurs marges par nature",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
		],
		'champs_libres' => [
			'nature_article_id' => [
				'nom' => "Nature",
				'type' => 20,
				'liste_choix' => 310,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'nature_article_modele_id' => [
				'nom' => "Modèle",
				'type' => 20,
				'liste_choix' => 311,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'marge' => [
				'nom' => "Marge",
				'type' => 3,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
		],
	];