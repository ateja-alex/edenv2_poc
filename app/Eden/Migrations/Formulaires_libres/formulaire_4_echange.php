<?php

return [
    'type_element' => 'echange',
    'titre_formulaire' => service('listes_formatees')->types_echange()[4],
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
            'taille_apres' => 6,
        ],
        [
            'type_element' => 'echange',
            'nom_sql' => 'description',
            'ordre' => 2,
            'taille_avant' => 0,
            'taille_libelle' => 2,
            'taille_champ' => 12,
            'taille_apres' => 0,

        ],
            [
                'type_element' => 'echange',
                'nom_sql' => 'contact_id',
                'ordre' => 3,
                'taille_avant' => 0,
                'taille_libelle' => 2,
                'taille_champ' => 4,
                'taille_apres' => 0,

            ],
        
    ],
];