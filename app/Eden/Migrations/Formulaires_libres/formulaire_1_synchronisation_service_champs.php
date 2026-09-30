<?php
return [
    'type_element' => 'synchronisation_service_champs',
    'titre_formulaire' => 'Formulaire EDEN => Service de synchronisation',
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "synchronisation_service_champs",
            'type_element' => "synchronisation_service_champs",
            'nom_sql' => "synchronisation_service_element_id",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "1",

        ],
        [
            'nom_formulaire' => "synchronisation_service_champs",
            'type_element' => "synchronisation_service_champs",
            'nom_sql' => "sens",
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "6",
            'ordre' => "2",
            'condition_affichage_v_if' => '0',
            'condition_affichage_en_v_show' => '1',
        ],
        [
            'type_element' => "synchronisation_service_champs",
            'nom_sql' => '',
            'taille_champ' => 12,
            'ordre' => 3,
            'type_champ' => 2,
            'nom_vue' => "parametrage_sens_0",
            'type_vue' => "standard",
        ],
        [
            'type_element' => "synchronisation_service_champs",
            'nom_sql' => '',
            'taille_champ' => 12,
            'ordre' => 4,
            'type_champ' => 2,
            'nom_vue' => "types_evenements",
            'type_vue' => "standard",
            'condition_affichage_v_if' => 'synchronisation_service_element && synchronisation_service_element.type_synchronisation == 0',
        ],
    ],
    'valeurs_par_defaut' => [
        [
            'nom_formulaire' => 'formulaire_1_synchronisation_service_champs',
            'nom_sql' => 'sens',
            'valeur' => '0',
        ],
    ],
];