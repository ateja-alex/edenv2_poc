<?php

return [
		'table_libre' => [
			'nom_table' => "Blog : articles",
			'nom_table_sql' => "blog_article",
			'description' => "",
			'feminin' => "",
			'element' => "article",
			'type_element' => "blog_article",
			'element_pluriel' => "articles",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#titre#',
		],
		'champs_libres' => [
			'titre' => [
				'nom' => "Titre",
				'recherche' => 1,
				'obligatoire' => 1,
			],
			'contenu' => [
				'nom' => "Contenu",
				'type' => 6,
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
			],
			'titre_seo' => [
				'nom' => "Titre seo",
			],
			'mots_cles_seo' => [
				'nom' => "Mots clés seo",
			],
			'description_seo' => [
				'nom' => "Description seo",
			],
			'image_principale' => [
				'nom' => "Image principale",
				'type' => 7,
			],
			'categorie_id' => [
				'nom' => "Catégorie",
				'type' => 20,
				'liste_choix' => 15,
				'obligatoire' => 1,
			],
			'url' => [
				'nom' => "Url",
				'obligatoire' => 1,
			],
		],
	];