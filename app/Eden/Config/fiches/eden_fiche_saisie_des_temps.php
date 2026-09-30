<?php

return [
    'modules' => [
        [
            'module' => 'entete',
            'afficher_par_defaut' => true,
            'cacher_bloc_v_if' => '1',
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        [
            'module' => 'saisie_element',
            'afficher_par_defaut' => true,
            'cacher_bloc_v_if' => '1',
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        [
            'module' => 'recapitulatif',
            'afficher_par_defaut' => true,
            'cacher_bloc_v_if' => '1',
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
    ],
    'colonne_droite' => [
    ],
    'options' => [
        'affichage_par_defaut' => '2',
        'unite' => 'heure',
        'jours' =>
            [
                0 => '0',
                1 => '1',
                2 => '3',
                3 => '4',
                4 => '2',
            ],
    ]
];