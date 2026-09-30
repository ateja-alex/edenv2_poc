<?php

return [
		'table_libre' => [
			'nom_table' => "Pays",
			'nom_table_sql' => "pays",
			'description' => "",
			'feminin' => "",
			'element' => "pays",
			'type_element' => "pays",
			'element_pluriel' => "pays",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
                'afficher_sur_formulaire' => 1,
            ],
			'code_iso' => [
				'nom' => "Code ISO",
                'afficher_sur_formulaire' => 1,
            ],
		],
	];