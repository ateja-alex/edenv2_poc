<?php

return [
		'table_libre' => [
			'nom_table' => "Rapports des tableaux de bord de type liste",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "rapport",
			'type_element' => "tableau_de_bord_liste_rapport",
			'element_pluriel' => "rapports",
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
			'tableau_de_bord_liste_categorie_id' => [
				'nom' => "Tableau de bord",
				'type' => 42,
				'obligatoire' => 1,
				'type_element_ajax' => 'tableau_de_bord_liste_categorie',
			],
			'id_rapport' => [
				'nom' => "Rapport",
				'obligatoire' => 1,
			],
		],
	];