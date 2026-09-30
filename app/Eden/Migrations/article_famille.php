<?php

return [
		'table_libre' => [
			'nom_table' => "article_famille",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "article famille",
			'type_element' => "article_famille",
			'element_pluriel' => "articles familles",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
		
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
			],
			'famille_id' => [
				'nom' => "Famille",
				'type' => 42,
				'type_element_ajax' => 'famille',
			],
            
		],
	];