<?php

return [
		'table_libre' => [
			'nom_table' => "Lots",
			'nom_table_sql' => "lot",
			'description' => "",
			'feminin' => "",
			'element' => "lot",
			'type_element' => "lot",
			'element_pluriel' => "lots",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'ERP',
            'affichage_dans_liste' => '#numero#',
		],
		'champs_libres' => [
			'numero' => [
				
				'nom' => 'Numéro de lot',
				'afficher_sur_formulaire' => 1,
				'recherche' => 1,
			],
			'date_peremption' => [
				'nom' => "Date de péremption",
				'type' => 4,
				'afficher_sur_formulaire' => 1,
			],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
				'afficher_sur_formulaire' => 1,
			],
		],
	];