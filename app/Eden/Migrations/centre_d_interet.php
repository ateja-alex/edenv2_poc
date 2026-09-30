<?php

return [
		'table_libre' => [
			'nom_table' => "Centres d'intérêt",
			'nom_table_sql' => "centre_d_interet",
			'description' => "",
			'feminin' => "",
			'element' => "centre d'intérêt",
			'type_element' => "centre_d_interet",
			'element_pluriel' => "centres d'intérêt",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'parent_id' => [
				'nom' => "Famille mere",
				'type' => 42,
				'type_element_ajax' => 'famille',
			],
			'nom' => [
				'nom' => "Nom",
				'afficher_sur_formulaire' => 1,
			],
		],
	];