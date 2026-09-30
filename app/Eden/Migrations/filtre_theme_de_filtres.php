<?php

return [
		'table_libre' => [
			'nom_table' => "Filtres (thèmes de filtres)",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "filtre",
			'type_element' => "filtre_theme_de_filtres",
			'element_pluriel' => "filtres",
			'fiche' => 0,
			
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
			'theme_de_filtres_id' => [
				'nom' => "Thème de filtres",
				'type' => 20,
				'liste_choix' => 94,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
		],
	];