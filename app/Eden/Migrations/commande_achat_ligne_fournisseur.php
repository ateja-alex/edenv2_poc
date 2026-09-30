<?php

return [
		'table_libre' => [
			'nom_table' => "Réceptions fournisseurs",
			'nom_table_sql' => "commande_achat_ligne_fournisseur",
			'description' => "",
			'feminin' => "",
			'element' => "commande fournisseur",
			'type_element' => "commande_achat_ligne_fournisseur",
			'element_pluriel' => "commandes fournisseurs",
			'fiche' => 0,
			'module' => 'Gestion commerciale',

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'article_id' => [
				'nom' => "Article",
				'type' => 42,
				'recherche' => 1,
				'type_element_ajax' => "article",
			],
			'date_de_reception' => [
				'nom' => "Date de réception",
				'type' => 4,
			],
			'recu' => [
				'nom' => "Reçue",
				'type' => 20,
				'liste_choix' => 14,
			],
			'quantite' => [
				'nom' => "Quantité",
				'type' => 3,
			],
			'commande_achat_id' => [
				'nom' => "Commande achat",
				'type' => 42,
				'type_element_ajax' => "commande_achat",
				'recherche' => 1,
			],
			'fournisseur_id' => [
				'nom' => "Fournisseur",
				'type' => 42,
				'type_element_ajax' => "fournisseur",
				'recherche' => 1,
			],
            'bl_achat_id' => [
                'nom' => "BL achat",
                'type' => 42,
                'type_element_ajax' => "bl_achat",
				'recherche' => 1,
            ],
            'marge_pourcentage' => [
				'nom' => 'Marge (%)',
				'type' => 3,
        	],
			'quantite_colisee' => [
				'nom' => "Quantité colisée",
				'type' => 0,
			],
			'conditionnement_id' => [
				'nom' => "Conditionnement",
				'type' => 42,
				'type_element_ajax' => "article_fournisseur",
			],
		],
	];