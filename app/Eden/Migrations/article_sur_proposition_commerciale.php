<?php 

return [
		'table_libre' => [
			'nom_table' => "Articles sur proposition commerciale",
			'nom_table_sql' => "article_sur_proposition_commerciale",
			'description' => "",
			'feminin' => "",
			'element' => "article sur proposition commerciale",
			'type_element' => "article_sur_proposition_commerciale",
			'element_pluriel' => "Articles sur proposition commerciale",
			'fiche' => "0",
			'creation_rapide' => "0",
			'disponible_recherche_rapide' => "0",
		],
		'champs_libres' => [
			
			'article_id' => [
				'type' => 42,
				'nom' => "Article",
				'obligatoire' => 1,
				'type_element_ajax' => "article",
				'afficher_sur_formulaire' => 1,
			],
			'proposition_commerciale_id' => [
				'type' => 42,
				'nom' => "Proposition commerciale",
				'obligatoire' => 1,
				'type_element_ajax' => "proposition_commerciale",
				'afficher_sur_formulaire' => 1,
			],
		],
	];