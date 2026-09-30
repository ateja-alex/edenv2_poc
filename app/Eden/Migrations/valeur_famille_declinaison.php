<?php

return [
		'table_libre' => [
			'nom_table' => "Valeur Famille déclinaison",
			'nom_table_sql' => "",
			'description' => "",
			'feminin' => "e",
			'element' => "valeur famille declinaison",
			'type_element' => "valeur_famille_declinaison",
			'element_pluriel' => "valeurs famille declinaisons",
			'fiche' => 0,

			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'module' => 'Gestion commerciale',
		],
		'champs_libres' => [

            'nom' => [
				'nom' => "Nom",
				'obligatoire' => 1,
			],
			'famille_declinaison' => [
				'nom' => "Famille déclinaison",
				'type' => 20,
			],
			
		],
	];