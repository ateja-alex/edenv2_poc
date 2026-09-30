<?php

return [
		'table_libre' => [
			'nom_table' => "Contenus (textes et images) sur le site",
			'nom_table_sql' => "contenu_site",
			'description' => "",
			'feminin' => "",
			'element' => "contenu",
			'type_element' => "contenu_site",
			'element_pluriel' => "contenus",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'texte' => [
				'nom' => "Texte",
				'type' => 6,
			],
			'langue_id' => [
				'nom' => "Langue",
				'type' => 20,
				'liste_choix' => 26,
			],
			
		],
	];