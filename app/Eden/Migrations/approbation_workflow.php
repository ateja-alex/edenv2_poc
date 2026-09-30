<?php

return [
		'table_libre' => [
			'nom_table' => "Approbation workflow",
			'nom_table_sql' => "approbation_workflow",
			'description' => "",
			'feminin' => "",
			'element' => "approbation_workflow",
			'type_element' => "approbation_workflow",
			'element_pluriel' => "approbations workflow",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,

		],
		'champs_libres' => [
			'type_element' => [
				'nom' => "Type élement",
				'afficher_sur_formulaire' => 1,
			],
			'profils_necessitant_approbation' => [
				'nom' => "Profils nécessitant une approbation",
				'type' => 10,
				'type_element_ajax' => 'profil',
				'afficher_sur_formulaire' => 1,
			],
			'action' => [
				'nom' => "Action",
				'type' => 20,
				'liste_choix' => 131,
				'afficher_sur_formulaire' => 1,
			],
			'approbation_en_cascade' => [
				'nom' => "Approbation en cascade",
				'type' => 20,
				'liste_choix' => 14,
				'afficher_sur_formulaire' => 1,
			],
		],
	];