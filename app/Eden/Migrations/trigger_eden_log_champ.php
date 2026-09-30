<?php

return [
	'table_libre' => [
		'nom_table' => "Trigger eden log champ",
		'nom_table_sql' => "trigger_eden_log_champ",
		'description' => "",
		'feminin' => "",
		'element' => "log champ",
		'type_element' => "trigger_eden_log_champ",
		'element_pluriel' => "logs champs",
		'fiche' => 0,

		'disponible_recherche_rapide' => 0,
		'creation_rapide' => 0,
        'affichage_dans_liste' => '#libelle#',
	],
	'champs_libres' => [
		'trigger_eden_id' => [
			'nom' => "Trigger eden",
            'type' => 42,
            'type_element_ajax' => 'trigger_eden'
		],
        'champ' => [
            'nom' => 'Champ'
        ]
	],
];