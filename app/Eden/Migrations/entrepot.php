<?php

return [
		'table_libre' => [
			'nom_table' => "Entrepots",
			'nom_table_sql' => "entrepot",
			'description' => "",
			'feminin' => "",
			'element' => "entrepot",
			'type_element' => "entrepot",
			'element_pluriel' => "entrepots",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
            'affichage_dans_liste' => '#nom#',
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			'articles_reserves' => [
				'nom' => 'Articles réservés',
				'type' => 20,
				'liste_choix' => 14,
			],
            'adresse_interne' => [
                'nom' => "Adresse interne",
                'type' => 42,
                'type_element_ajax' => "adresse_interne",
            ],
		],
	];
