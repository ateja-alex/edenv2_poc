<?php

return [
    'vue_js' => [
		'vuejs_data' => "",
		'vuejs_methods' => "",
	],
    'champs_libres' => [
        [
            'type_element' => "integration_email",
            'nom_sql' => "nom",
            'ordre' => 1,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 6,
        ],
        [
            'type_element' => "integration_email",
            'nom_sql' => "",
            'ordre' => 2,
            'taille_avant' => 0,
            'taille_libelle' => 0,
            'taille_champ' => 12,
            'taille_apres' => 0,
            'type_champ' => 2,
            'nom_vue' => "type_element",
            'type_vue' => "standard",
        ],
        [
            'type_element' => 'integration_email',
            'nom_sql' => '',
            'taille_avant' => '',
            'taille_libelle' => '',
            'taille_champ' => '',
            'taille_apres' => '',
            'ordre' => 4,
            'type_champ' => 3,
            'nom_sous_formulaire' => 'integration_email_sous_formulaire_integration_email_comptes_emails',
            'nom_affichage_sous_formulaire' => 'Comptes emails',
        ],
    ],


];
