<?php

return [
		'table_libre' => [
			'nom_table' => "Images des sliders",
			'nom_table_sql' => "image_slider",
			'description' => "",
			'feminin' => "",
			'element' => "image slider",
			'type_element' => "image_slider",
			'element_pluriel' => "images sliders",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
		],
		'champs_libres' => [
			'image' => [
				'nom' => "Image",
				'type' => 7,
				'obligatoire' => 1,
			],
			'date_debut' => [
				'nom' => "Début affichage",
				'obligatoire' => 1,
				'type' => 4,
			],
			'date_fin' => [
				'nom' => "Fin affichage",
				'obligatoire' => 1,
				'type' => 4,
			],
			'lien' => [
				'nom' => "Lien",
			],
		],
	];