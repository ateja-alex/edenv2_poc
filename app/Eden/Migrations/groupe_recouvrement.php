<?php

return [
	'table_libre' => [
		'nom_table' => "Groupes de clients recouvrement",
		'nom_table_sql' => "groupe_recouvrement",
		'description' => "",
		'feminin' => "",
		'element' => "groupe recouvrement",
		'type_element' => "groupe_recouvrement",
		'element_pluriel' => "groupes recouvrement",
		'fiche' => 0,
		
		'disponible_recherche_rapide' => 0,
		'creation_rapide' => 0,
        'affichage_dans_liste' => '#nom#',
	],
	'champs_libres' => [
		'nom' => [
			'nom' => "Nom",
			'obligatoire' => 1,
		],
		'relance_1' => [
			'nom' => "Nom relance 1",
			'obligatoire' => 1,
		],
		'relance_2' => [
			'nom' => "Nom relance 2",
			'obligatoire' => 1,
		],
		'relance_3' => [
			'nom' => "Nom relance 3",
			'obligatoire' => 1,
		],
		'relance_4' => [
			'nom' => "Nom relance 4",
			'obligatoire' => 1,
		],
		'delai_relance_1' => [
			'nom' => "Délai relance 1",
			'obligatoire' => 1,
			'type' => 2,
		],
		'delai_relance_2' => [
			'nom' => "Délai relance 2",
			'obligatoire' => 1,
			'type' => 2,
		],
		'delai_relance_3' => [
			'nom' => "Délai relance 3",
			'obligatoire' => 1,
			'type' => 2,
		],
		'delai_relance_4' => [
			'nom' => "Délai relance 4",
			'obligatoire' => 1,
			'type' => 2,
		],
		'par_defaut' => [
			'nom' => "Groupe par défaut",
			'type' => 20,
			'liste_choix' => 14,
		],
	],
];