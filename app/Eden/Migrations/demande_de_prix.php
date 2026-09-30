<?php

return [
		'table_libre' => [
			'nom_table' => "Demande de prix",
			'nom_table_sql' => "demande_de_prix",
			'description' => "",
			'feminin' => "",
			'element' => "demande de prix",
			'type_element' => "demande_de_prix",
			'element_pluriel' => "demande de prix",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
       
            'titre' => [
                'nom' => "Titre",
			],
			'description' => [
				'nom' => "Description demande",
				'type' => 6,
			],
			'statut' => [
				'nom' => "Statut",
                'type' => 20,
                'liste_choix' => 63,
			],
            'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => 'client',
            ],
            'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
            'projet_id' => [
				'nom' => "Projet",
				'type' => 42,
				'type_element_ajax' => 'projet',
			],
			'utilisateur_id' => [
                'nom' => "Responsable",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
            ],
            'date' => [
				'nom' => "Date",
				'type' => 4,
			],
	
		],
	];