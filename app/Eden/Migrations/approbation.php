<?php

return [
		'table_libre' => [
			'nom_table' => "Approbation",
			'nom_table_sql' => "approbation",
			'description' => "",
			'feminin' => "",
			'element' => "approbation",
			'type_element' => "approbation",
			'element_pluriel' => "approbations",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'utilisateur_id' => [
				'nom' => "Demandeur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			'destinataire_id' => [
				'nom' => "Destinataire",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			'type_element' => [
				'nom' => "Type élement",
			],
			'element_id' => [
				'nom' => "Element",
				'type' => 2,
			],
			'action' => [
				'nom' => "Action",
				'type' => 20,
				'liste_choix' => 131,
			],
			'approbation' => [
				'nom' => "Approbation",
				'type' => 20,
				'liste_choix' => 3,
			],
			'date_approbation' => [
				'nom' => "Date d'approbation",
				'type' => 5,
			],
			'commentaire_refus' => [
				'nom' => "Commentaire du refus",
				'type' => 6,
			],
		],
	];