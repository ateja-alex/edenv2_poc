<?php

return [
		'table_libre' => [
			'nom_table' => "Utilisations coupons réductions",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "utilisation coupon réduction",
			'type_element' => "coupon_reduction_client",
			'element_pluriel' => "coupons réduction",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => "client",
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
			'coupon_reduction_id' => [
				'nom' => "Coupon de réduction",
				'type' => 42,
				'type_element_ajax' => "coupon_reduction",
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
			],
		],
	];