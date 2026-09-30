<?php

return [
		'table_libre' => [
			'nom_table' => "Echéances",
			'nom_table_sql' => "echeance",
			'description' => "",
			'feminin' => "e",
			'element' => "écheance",
			'type_element' => "echeance",
			'element_pluriel' => "écheances",
			'fiche' => 0,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
            'titre' => [
                'nom' => "Titre",
            ],
			'type_element' => [
				'nom' => "Type de document",
                'type' => 21,
                'contenu' => '[{"type_element":"facture_vente","valeur":true}]'
			],
			'element_id' => [
				'nom' => "Id du document",
				'type' => 22,
                'contenu' => 'type_element'
			],
			'montant' => [
				'nom' => "Montant",
				'type' => 3,
			],
			'pourcentage' => [
				'nom' => "Pourcentage",
				'type' => 20,
                'liste_choix' => 14,
                'valeur_defaut' => 0,
			],
			'montant_pourcentage' => [
				'nom' => "Montant pourcentage",
				'type' => 3,
			],
			'date' => [
				'nom' => "Date",
				'type' => 4,
			],
		],
	];