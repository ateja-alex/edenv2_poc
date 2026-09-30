<?php

return [
		'table_libre' => [
			'nom_table' => "Colonnes pour les export comptables",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "colonne",
			'type_element' => "export_compta_colonne",
			'element_pluriel' => "colonnes",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Comptabilité',
		],
		'champs_libres' => [
			'valeur' => [
				'nom' => "Valeur",
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'ordre' => [
				'nom' => "Ordre",
				'type' => 2,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'titre' => [
				'nom' => "Titre",
				'type' => 0,
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'export_compta_modele_id' => [
				'nom' => "Modèle",
				'type' => 42,
				'type_element_ajax' => 'export_compta_modele',
				'obligatoire' => 1,
				'afficher_sur_formulaire' => 1,
			],
			'positionne_longueur_champs' => [
				'nom' => "Longeur du champ",
				'type' => 2,
				'obligatoire' => 0,
				'afficher_sur_formulaire' => 1,
			],
			'positionne_alignement' => [
				'nom' => "Alignement de la valeur",
				'type' => 20,
				'liste_choix' => 523,
				'obligatoire' => 0,
				'afficher_sur_formulaire' => 1,
			],
		],
	];