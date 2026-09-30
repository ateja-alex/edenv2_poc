<?php

return [
		'table_libre' => [
			'nom_table' => "MMessages sur discussions",
			'nom_table_sql' => "message",
			'description' => "",
			'feminin' => "",
			'element' => "message",
			'type_element' => "message",
			'element_pluriel' => "messages",
			'fiche' => 0,
			'parametre' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'auteur' => [
				'nom' => "Auteur",
                'type' => 42,
                'type_element_ajax' => 'utilisateur',
			],
			'type_element' => [
				'nom' => "Type element",
			],
			'element_id' => [
				'nom' => "Element id",
				'type' => 2,
			],
			'contenu' => [
				'nom' => "Contenu",
				'type' => 6,
				'afficher_sur_formulaire' => 1,
			],
			'piece_jointe' => [
				'nom' => "Pièce jointe",
				'type' => 7,
				'afficher_sur_formulaire' => 1,
			],
            'commentaire' => [
                'nom' => "message",
                'type' => 6,
                'afficher_sur_formulaire' => 1,
            ],
		],
	];