<?php

return [
		'table_libre' => [
			'nom_table' => "Thèmes de filtres",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "thème de filtres",
			'type_element' => "theme_de_filtres",
			'element_pluriel' => "thèmes de filtres",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'familles' => [
				'nom' => "Familles",
				'type' => 10,
				'type_element_ajax' => 'famille',
				'afficher_sur_formulaire' => 1,
			],
		],
	];