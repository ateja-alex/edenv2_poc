<?php

return [
		'table_libre' => [
			'nom_table' => "Catégories des tableaux de bord de type liste",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "catégorie",
			'type_element' => "tableau_de_bord_liste_categorie",
			'element_pluriel' => "catégories",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'tableau_de_bord_id' => [
				'nom' => "Tableau de bord",
				'type' => 42,
				'obligatoire' => 1,
				'type_element_ajax' => 'tableau_de_bord',
			],
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],
			
			
		],
	];