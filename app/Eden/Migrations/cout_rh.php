<?php

return [
		'table_libre' => [
			'nom_table' => "Coûts RH",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "coût RH",
			'type_element' => "cout_rh",
			'element_pluriel' => "coûts RH",
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
				'nom' => "Coût",
				'type' => 3,
				'obligatoire' => 1,
			],
			/*
			'employe' => [
				'nom' => "Employé",
				'type' => 20,
				'liste_choix' => 17,
				'obligatoire' => 0,
			],
			*/
			'utilisateur_id' => [
				'nom' => "Utilisateur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
				'obligatoire' => 1,
			],
			'salaire_brut_charge' => [
				'nom' => "Salaire brut chargé",
				'type' => 3,
			],
		],
	];