<?php

return [
		'table_libre' => [
			'nom_table' => "Mouvements de stock",
			'nom_table_sql' => "mouvement_de_stock",
			'description' => "",
			'feminin' => "",
			'element' => "mouvement de stock",
			'type_element' => "mouvement_de_stock",
			'element_pluriel' => "mouvements de stock",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'date' => [
				'nom' => "Date",
				'type' => 4,
				'recherche' => 1,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'entrepot_id' => [
				'nom' => "Entrepot",
				'type' => 42,
				'type_element_ajax' => 'entrepot',
				'afficher_sur_formulaire' => 1,
			],
			'type_document' => [
				'nom' => "Type de document",
			],
			'document_id' => [
				'nom' => "Document",
				'type' => 2,
			],
			'ligne_id' => [
				'nom' => "Ligne",
				'type' => 2,
			],
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "article",
				'afficher_sur_formulaire' => 1,
			],
			'projet_id' => [
				'nom' => "Projet",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "projet",
				'afficher_sur_formulaire' => 1,
			],
			'quantite' => [
				'nom' => "Quantité",
				'type' => 3,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'quantite_conditionnement' => [
				'nom' => 'Quantité Conditionnement',
				'type' => 3,
			],
			'conditionnement_id' => [
				'nom' => 'Conditionnement',
				'type' => 42,
				'type_element_ajax' => 'conditionnement'
			],
			'reserve' => [
				'nom' => "Réservé ",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
			],
			'type_de_mouvement' => [
				'nom' => 'Type de mouvement', 
				'type' => 20,
				'liste_choix' => 109,
				'afficher_sur_formulaire' => 1,

			],
			'transformation_stocks_id' => [
				'nom' => 'Transformation stock',
				'type' => 3,
			],
			'transformation_stock_total' => [
				'nom' => 'Transformation stock total',
				'type' => 3,
			],
		],
	];
