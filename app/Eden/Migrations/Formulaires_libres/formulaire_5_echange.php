<?php

return [
    'type_element' => 'echange',
    'titre_formulaire' => service('listes_formatees')->types_echange()[5],
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "1",
    ],
        'champs_libres' => [[

            'type_element' => 'echange',
            'nom_sql' => 'date',
            'ordre' => 1,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 0,
        ],
        [
            'type_element' => 'echange',
            'nom_sql' => 'date_fin',
            'ordre' => 2,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 0,

        ],
        [
            'type_element' => 'echange',
            'nom_sql' => 'objet',
            'ordre' => 3,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 4,
            'taille_apres' => 0,

        ],
            [
                'type_element' => 'echange',
                'nom_sql' => 'emplacement',
                'ordre' => 4,
                'taille_avant' => 0,
                'taille_libelle' => 2,
                'taille_champ' => 4,
                'taille_apres' => 0,

            ],
            [
                'type_element' => 'echange',
                'nom_sql' => 'description',
                'ordre' => 5,
                'taille_avant' => 0,
                'taille_libelle' => 2,
                'taille_champ' => 12,
                'taille_apres' => 0,

            ],
            [
                'type_element' => 'echange',
                'nom_sql' => 'contact_id',
                'ordre' => 6,
                'taille_avant' => 0,
                'taille_libelle' => 2,
                'taille_champ' => 4,
                'taille_apres' => 0,

            ],
            [
                'type_element' => 'echange',
                'nom_sql' => 'projet_id',
                'ordre' => 7,
                'taille_avant' => 0,
                'taille_libelle' => 2,
                'taille_champ' => 4,
                'taille_apres' => 0,

            ],
            [
                'type_element' => 'echange',
                'nom_sql' => 'utilisateur_id',
                'ordre' => 8,
                'taille_avant' => 0,
                'taille_libelle' => 2,
                'taille_champ' => 4,
                'taille_apres' => 0,

            ],
    ],
];