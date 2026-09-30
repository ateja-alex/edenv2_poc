<?php

return [
		'table_libre' => [
			'nom_table' => "Transaction paiement",
			'nom_table_sql' => "transaction_paiement",
			'description' => "",
			'feminin' => "",
			'element' => "transaction",
			'type_element' => "transaction_paiement",
			'element_pluriel' => "transactions",
			'fiche' => 0,

			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'prestataire' => [
				'nom' => "Prestataire",
			],
			'factures' => [
				'nom' => "Factures",
			],
			'type' => [
				'nom' => "Type",
			],
		],
	];