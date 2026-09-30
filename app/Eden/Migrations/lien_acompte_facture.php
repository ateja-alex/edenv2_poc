<?php

return [
		'table_libre' => [
			'nom_table' => "Liens acompte facture",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "Lien acompte facture",
			'type_element' => "lien_acompte_facture",
			'element_pluriel' => "Liens acompte facture",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'facture_vente_id' => [
				'nom' => "Facture",
				'type' => 42,
				'type_element_ajax' => 'facture_vente',
			],
			'acompte_vente_id' => [
				'nom' => "Acompte",
				'type' => 42,
				'type_element_ajax' => 'acompte_vente',
			],
			
			
		],
	];