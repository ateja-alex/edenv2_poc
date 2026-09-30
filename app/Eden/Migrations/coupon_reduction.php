<?php

return [
		'table_libre' => [
			'nom_table' => "Coupons réductions",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "coupon réduction",
			'type_element' => "coupon_reduction",
			'element_pluriel' => "coupons réduction",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 1,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'debut_validite' => [
				'nom' => "Début validité",
				'type' => 4,
			],
			'fin_validite' => [
				'nom' => "Fin validité",
				'type' => 4,
			],
			'code' => [
				'nom' => "Code",
			],
			'valeur' => [
				'nom' => "Valeur",
				'type' => 3,
				'obligatoire' => 1,
			],
			'type_de_reduction' => [
				'nom' => "Type de réduction",
				'type' => 20,
				'liste_choix' => 16,
			],
			'nouveau_client_seulement' => [
				'nom' => "Nouveau client seulement",
				'type' => 20,
				'liste_choix' => 14,
			],
			'nominatif' => [
				'nom' => "Nominatif",
				'type' => 20,
				'liste_choix' => 14,
			],
			'usage_unique' => [
				'nom' => "Usage unique",
				'type' => 20,
				'liste_choix' => 14,
			],
			'utilise' => [
				'nom' => "Utilisé",
				'type' => 20,
				'liste_choix' => 14,
			],
			'client_id' => [
				'nom' => "Client",
				'type' => 42,
				'type_element_ajax' => "client",
			],
			'minimum_de_commande' => [
				'nom' => "Minimum de commande",
				'type' => 3,
			],
			'type_coupon' => [
				'nom' => "Type de coupon",
				'type' => 20,
				'liste_choix' => 55,
				'obligatoire' => 1,
			],
			'liste_familles_coupon' => [
				'nom' => "Liste des familles",
				'type' => 6,
			],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
			],
		],
	];