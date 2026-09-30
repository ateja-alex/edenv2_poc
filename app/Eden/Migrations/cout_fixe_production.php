<?php

return [
		'table_libre' => [
			'nom_table' => "Coûts fixes de production",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "coût fixe",
			'type_element' => "cout_fixe_production",
			'element_pluriel' => "coûts fixes",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'date_de_prise_en_compte' => [
				'nom' => "Date de prise en compte",
				'type' => 4,
				'obligatoire' => 1,
			],
			'cout' => [
				'nom' => "Coût horaire",
				'type' => 3,
				'obligatoire' => 1,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
				'obligatoire' => 1,
			],
		],
	];