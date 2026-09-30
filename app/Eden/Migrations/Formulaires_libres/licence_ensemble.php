<?php

return [
    'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
	],
    'champs_libres' => [
        [

			'type_element' => 'licence_ensemble',
			'nom_sql' => 'nom',
			'ordre' => 1,
			'taille_avant' => 0,
			'taille_libelle' => 2,
			'taille_champ' => 4,
			'taille_apres' => 0,
		],
        [
            'type_element' => "licence_ensemble",
            'nom_sql' => "",
            'ordre' => 2,
            'taille_avant' => 0,
            'taille_libelle' => 0,
            'taille_champ' => 12,
            'taille_apres' => 0,
            'type_champ' => 2,
            'nom_vue' => "choix_elements",
            'type_vue' => "standard",
        ],
    ],


];
