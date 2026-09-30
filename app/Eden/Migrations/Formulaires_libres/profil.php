<?php
return [
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "profil",
            'type_element' => "profil",
            'nom_sql' => "nom",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "1",
            'condition_affichage_v_if' => '!(profil.id > 0)'

        ],
        [
            'nom_formulaire' => "profil",
            'type_element' => "profil",
            'nom_sql' => "extranet",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "2",

        ],
    ]
];