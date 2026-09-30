<?php

return [
		'table_libre' => [
			'nom_table' => "Paniers",
			'nom_table_sql' => "panier",
			'description' => "",
			'feminin' => "",
			'element' => "panier",
			'type_element' => "panier",
			'element_pluriel' => "paniers",
			'fiche' => 1,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
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
			
			'coupon_reduction_id' => [
				'nom' => "Coupon de réduction",
				'type' => 42,
				'type_element_ajax' => 'coupon_reduction',
			],
			'session_id' => [
				'nom' => "Session id",
			],
			'modalite_paiement_id' => [
				'nom' => "Modalité de paiement",
				'type' => 20,
				'liste_choix' => 6,
			],
			'mode_paiement_id' => [
				'nom' => "Mode de paiement",
				'type' => 20,
				'liste_choix' => 7,
			],
			'statut' => [
				'nom' => "Statut",
				'type' => 20,
				'liste_choix' => 34,
			],
		],
	];