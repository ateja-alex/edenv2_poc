<?php

return [
		'table_libre' => [
			'nom_table' => "tableau_de_bord_contenu_correspondance_filtres",
			'nom_table_sql' => "tableau_de_bord_contenu_correspondance_filtres",
			'description' => "",
			'element' => "tableau_de_bord_contenu_correspondance_filtres",
			'type_element' => "tableau_de_bord_contenu_correspondance_filtres",
			'element_pluriel' => "tableau_de_bord_contenu_correspondance_filtres",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'tableau_de_bord_contenu' => [
				'nom' => "Tableau de bord contenu",
				'type' => 42,
                'type_element_ajax' => 'tableau_de_bord_contenu'
			],
			'id_filtre_tableau_de_bord' => [
				'nom' => "Filtre tableau de bord",
				'type' => 2,
			],
			'nom_sql_compatible' => [
				'nom' => "Champ compatible",
			],
		],
	];