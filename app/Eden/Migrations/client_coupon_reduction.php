<?php

return [
		'table_libre' => [
			'nom_table' => "Coupon réduction par client",
			'nom_table_sql' => "client_coupon_reduction",
			'description' => "",
			'feminin' => "",
			'element' => "Coupon réduction par client",
			'type_element' => "client_coupon_reduction",
			'element_pluriel' => "Coupons réductions par client",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'client_id' => [
				'nom' => "Client, ",
				'type' => 42,
                'obligatoire' => 1,
				'type_element_ajax' => "client",
            ],
            'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
            'coupon_reduction_id' => [
				'nom' => "Coupon réduction",
				'type' => 42,
                'obligatoire' => 1,
				'type_element_ajax' => "coupon_reduction",
            ],
            'facture_id' => [
				'nom' => "Facture",
				'type' => 42,
                'obligatoire' => 1,
				'type_element_ajax' => "facture_vente",
            ],
            'date' => [
				'nom' => "Date",
				'type' => 4,
			],
		],
	];