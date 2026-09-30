<?php

return [
		'table_libre' => [
			'nom_table' => "rapport_abonnement",
			'nom_table_sql' => "rapport_abonnement",
			'description' => "",
			'feminin' => "",
			'element' => "abonnement",
			'type_element' => "rapport_abonnement",
			'element_pluriel' => "abonnements",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'liste_id' => [
				'nom' => "Liste libre",
				'type' => 2
			],
			'utilisateur_id' => [
				'nom' => "Utilisateur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			'nom' => [
				'nom' => "Nom",
			],
			'parametres' => [
				'nom' => "Paramètres",
			],
			'frequence' => [
				'nom' => "Fréquence",
			],
			'frequence_detail' => [
				'nom' => "Détail de la fréquence",
			],
			'type' => [
				'nom' => "Type",
			],
			'id_rapport' => [
				'nom' => "ID rapport",
			],
		],
	];