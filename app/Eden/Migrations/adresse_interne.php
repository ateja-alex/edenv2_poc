<?php

return [
		'table_libre' => [
			'nom_table' => "Adresses internes",
			'nom_table_sql' => "adresse_interne",
			'description' => "",
			'feminin' => "e",
			'element' => "adresse interne",
			'type_element' => "adresse_interne",
			'element_pluriel' => "adresses internes",
			'fiche' => 0,
			'disponible_recherche_rapide' => 0,
			'creation_rapide' => 0,
			'parametre' => 1,
		],
		'champs_libres' => [
			'societe' => [
				'nom' => "Société",
				'recherche' => 1,
			],
			'adresse' => [
				'nom' => "Adresse",
				'recherche' => 1,
			],
			'adresse_complement' => [
				'nom' => "Complément",
				'recherche' => 1,
			],
			'ville' => [
				'nom' => "Ville",
				'recherche' => 1,
			],
			'code_postal' => [
				'nom' => "Code postal",
				'recherche' => 1,
			],
			'nom_adresse' => [
				'nom' => "Nom adresse",
			],
			'nom' => [
				'nom' => "Nom",
			],
			'prenom' => [
				'nom' => "Prénom",
			],
			'pays_id' => [
				'nom' => "Pays",
				'type' => 20,
				'liste_choix' => 28,
			],
			'type_adresse' => [
				'nom' => "Type adresse",
				'type' => 20,
				'liste_choix' => 31,
				'type_element_ajax' => "adresse",
			],
			'telephone_portable' => [
				'nom' => "Téléphone portable",
			],
			'telephone_fixe' => [
				'nom' => "Téléphone fixe",
			],
			'message_enregistrement_adresse' => [
				'nom' => "message_enregistrement_adresse",
			],
			'par_defaut' => [
				'nom' => "Par défaut",
				'type' => 20,
				'liste_choix' => 14,
			],
		],
	];