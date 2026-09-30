<?php

    return [
		'table_libre' => [
			'nom_table' => "Relance recouvrement",
			'nom_table_sql' => "relance_recouvrement",
			'description' => "",
			'feminin' => "",
			'element' => "relance recouvrement",
			'type_element' => "relance_recouvrement",
			'element_pluriel' => "relances recouvrements",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			
			'date' => [
				'nom' => "Date",
				'obligatoire' => 1,
				'type' => 4,
			],
			'client_id' => [
				'nom' => "Client",
				'obligatoire' => 1,
				'type' => 42,
				'type_element_ajax' => 'client',
            ],
			'entite_id' => [
				'nom' => "Entité",
                'type' => 42,
                'type_element_ajax' => 'entite',
            ],
			'a_faire' => [
				'nom' => "A faire",
				'type' => 20,
				'liste_choix' => 14,
            ],
            'facture_vente_id' => [
				'nom' => "Facture vente",
				'type' => 42,
				'type_element_ajax' => 'facture_vente',
            ],
            'acompte_vente_id' => [
				'nom' => "Acompte vente",
				'type' => 42,
				'type_element_ajax' => 'acompte_vente',
            ],
            'type_relance' => [
				'nom' => "Type relance",
				'obligatoire' => 1,
                'type' => 20,
                'liste_choix' => 57,
			],
			'type_element' => [
				'nom' => 'Type element'
			],
	
		],
	];