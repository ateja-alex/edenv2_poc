<?php

return [
    'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
	],
    'champs_libres' => [
        [
            'type_element' => "integration_email_correspondance",
            'nom_sql' => "integration_email_id",
            'ordre' => 1,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 6,
            'type_champ' => 0,
        ],
        [
            'type_element' => "integration_email_correspondance",
            'nom_sql' => "mail_reponse",
            'ordre' => 2,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 6,
            'type_champ' => 0,
            'condition_affichage_v_if' => 'integration_email.table_gestion_reponse != null'
        ],
        [
            'type_element' => "integration_email_correspondance",
            'nom_sql' => "",
            'ordre' => 3,
            'taille_avant' => 0,
            'taille_libelle' => 0,
            'taille_champ' => 12,
            'taille_apres' => 0,
            'type_champ' => 2,
            'nom_vue' => "gestion_valeur",
            'type_vue' => "standard",
        ],
    ],


];
