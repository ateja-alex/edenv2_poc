<?php

return [
		'table_libre' => [
			'nom_table' => "Blog : catégories",
			'nom_table_sql' => "blog_categorie",
			'description' => "",
			'feminin' => "e",
			'element' => "catégorie",
			'type_element' => "blog_categorie",
			'element_pluriel' => "catégories",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'recherche' => 1,
				'obligatoire' => 1,
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
			'description' => [
				'nom' => "Description",
				'type' => 6,
			],
			'titre' => [
				'nom' => "Titre",
			],
			'url' => [
				'nom' => "Url",
				'obligatoire' => 1,
			],
		],
	];