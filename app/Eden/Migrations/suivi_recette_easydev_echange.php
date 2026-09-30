<?php

return [
		'table_libre' => [
			'nom_table' => "Echange Suivi recette Easy Développement",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "",
			'element' => "echange",
			'type_element' => "suivi_recette_easydev_echange",
			'element_pluriel' => "echanges",
			'fiche' => 1,
			
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [
			'suivi_recette' => [
				'nom' => "Bug",
				'type' => 42,
				'obligatoire' => 1,
				'type_element_ajax' => "suivi_recette_easydev",
			],
			'message' => [
				'nom' => "Message",
				'type' => 6,
			],
			'piece_jointe' => [
				'nom' => "Pièce jointe",
				'type' => 7,
			],
			'url' => [
				'nom' => "URL",
                'format_champ' => "url",
			],
			
		],
	];