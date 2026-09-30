<?php

return [
    'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
	],
    'champs_libres' => [
        [
            'type_element' => "integration_email_comptes_emails",
            'nom_sql' => "cle_locale",
            'ordre' => 1,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 6,
            'type_champ' => 0,
        ],
        [
            'type_element' => "integration_email_comptes_emails",
            'nom_sql' => "valeur",
            'ordre' => 2,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 6,
            'type_champ' => 0,
        ],
        [
            'type_element' => "integration_email_comptes_emails",
            'nom_sql' => "",
            'ordre' => 3,
            'taille_avant' => 0,
            'taille_libelle' => 0,
            'taille_champ' => 12,
            'taille_apres' => 0,
            'type_champ' => 2,
            'nom_vue' => "boites_mails",
            'type_vue' => "standard",
        ],
    ],


];
