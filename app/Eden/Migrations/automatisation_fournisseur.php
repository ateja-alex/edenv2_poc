<?php

return [
		'table_libre' => [
			'nom_table' => "Automatisations fournisseurs",
			'nom_table_sql' => "automatisation_fournisseur",
			'description' => "",
			'feminin' => "e",
			'element' => "automatisation",
			'type_element' => "automatisation_fournisseur",
			'element_pluriel' => "automatisations",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'type_element_ajax' => 'fournisseur',
			],
			'mot_cle' => [
				'nom' => "Mot clé",
			],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
			],
			'taux_tva' => [
				'nom' => "Taux de tva",
				'type' => 3,
			],
			
		],
	];