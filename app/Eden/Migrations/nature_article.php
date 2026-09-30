<?php

return [
		'table_libre' => [
			'nom_table' => "Natures des articles",
			'nom_table_sql' => "nature_article",
			'description' => "",
			'feminin' => "",
			'element' => "nature d'article",
			'type_element' => "nature_article",
			'element_pluriel' => "natures des articles",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'marge_par_defaut' => [
				'nom' => "Marge par défaut",
				'type' => 3,
				'afficher_sur_formulaire' => 1,
			],
		],
	];