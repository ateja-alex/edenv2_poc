<?php

return [
		'table_libre' => [
			'nom_table' => "Changement des tarifs",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "changement",
			'type_element' => "changement_tarif",
			'element_pluriel' => "changements",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => "article",
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'date' => [
				'nom' => "Date",
				'obligatoire' => 1,
				'type' => 4,
				'afficher_sur_formulaire' => 1,
			],
			'tarif' => [
				'nom' => "Tarif",
				'obligatoire' => 1,
				'type' => 3,
				'afficher_sur_formulaire' => 1,
			],
			'traite' => [
				'nom' => "Traité",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
			],
		],
	];