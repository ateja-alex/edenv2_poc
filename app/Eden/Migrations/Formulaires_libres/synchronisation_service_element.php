<?php
return [
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "synchronisation_service_element",
            'type_element' => "synchronisation_service_element",
            'nom_sql' => "synchronisation_service_id",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "1",
            'condition_lecture_seule' => "synchronisation_service_element.id > 0"

        ],
        [
            'nom_formulaire' => "synchronisation_service_element",
            'type_element' => "synchronisation_service_element",
            'nom_sql' => "type_synchronisation",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "2",
            'condition_lecture_seule' => "synchronisation_service_element.id > 0"
        ],
        [
            'type_element' => "synchronisation_service_element",
            'nom_sql' => '',
            'taille_champ' => 12,
            'ordre' => 3,
            'type_champ' => 2,
            'nom_vue' => "parametrage",
            'type_vue' => "standard",
        ],
    ],
];