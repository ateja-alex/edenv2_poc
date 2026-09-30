<?php

return [
		'table_libre' => [
			'nom_table' => "Modalités de paiement",
			'nom_table_sql' => "modalite_paiement",
			'description' => "",
			'feminin' => "",
			'element' => "modalité de paiement",
			'type_element' => "modalite_paiement",
			'element_pluriel' => "modalité de paiement",
			'fiche' => 0,
			'parametre' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			'fin_de_mois' => [
				'nom' => "Fin de mois",
				'type' => 20,
				'liste_choix' => 14,
			],
			'nombre_de_jours' => [
				'nom' => "Nombre de jours",
				'type' => 2,
			],
			'le' => [
				'nom' => "Le",
				'type' => 2,
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
			],
		],
	];