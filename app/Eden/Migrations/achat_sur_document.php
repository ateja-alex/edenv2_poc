<?php

return [
		'table_libre' => [
			'nom_table' => "Achats sur les documents",
			'nom_table_sql' => "achat_sur_document",
			'description' => "",
			'feminin' => "",
			'element' => "Achat sur document",
			'type_element' => "achat_sur_document",
			'element_pluriel' => "Achats sur document",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'type_element_ajax' => 'fournisseur',
			],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'type_element_ajax' => 'article',
			],
			'designation' => [
				'nom' => "Désignation",
			],
			'quantite' => [
				'nom' => "Quantité",
				'type' => 2,
			],
			'tarif' => [
				'nom' => "Tarif",
				'type' => 3,
			],
			'remise' => [
				'nom' => "Remise",
				'type' => 3,
			],
			'tva' => [
				'nom' => "tva",
				'type' => 3,
			],
			'type_element' => [
				'nom' => "Type élément",
			],
			'document_id' => [
				'nom' => "Document ID",
				'type' => 2,
			],
			'projet_id' => [
				'nom' => "Projet",
				'type' => 42,
				'type_element_ajax' => 'projet',
			],
			'ligne' => [
				'nom' => "Ligne",
				'type' => 2,
			],
			
		],
	];