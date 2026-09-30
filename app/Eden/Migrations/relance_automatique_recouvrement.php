<?php

return [
		'table_libre' => [
			'nom_table' => "relance_automatique_recouvrement",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "relance automatique recouvrement",
			'type_element' => "relance_automatique_recouvrement",
			'element_pluriel' => "Relances automatiques recouvrement",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [

			'type_relance' => [
				'nom' => "Type de relance",
                'type' => 20,
				'liste_choix' => 57,
				'afficher_sur_formulaire' => 1,
			],

			'difference_date' => [
				'nom' => "Différence de Date",
				'afficher_sur_formulaire' => 1,
				'type' => 2,
			],

			'modele_email_id' => [
				'nom' => "Modèle d'email",
                'type' => 42,
				'type_element_ajax' => 'modele_email',
				'afficher_sur_formulaire' => 1,
			],
			        
		],
	];