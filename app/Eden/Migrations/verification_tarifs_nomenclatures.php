<?php

return [
		'table_libre' => [
			'nom_table' => "Vérification des nomenclatures de tarifs",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "tarif",
			'type_element' => "verification_tarifs_nomenclatures",
			'element_pluriel' => "tarifs",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'article_id_nomenclature' => [
				'nom' => "Article Nomenclature",
				'type' => 42,
				'type_element_ajax' => "article",
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'article_id_modifie' => [
				'nom' => "Article Modifié",
				'type' => 42,
				'type_element_ajax' => "article",
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'ancien_tarif' => [
				'nom' => "Ancien tarif",
				'obligatoire' => 1,
				'type' => 3,
				'afficher_sur_formulaire' => 1,
			],
			'nouveau_tarif' => [
				'nom' => "Nouveau tarif",
				'obligatoire' => 1,
				'type' => 3,
				'afficher_sur_formulaire' => 1,
			],
		],
	];