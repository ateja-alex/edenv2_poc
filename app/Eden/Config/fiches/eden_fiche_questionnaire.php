<?php

return [
    'modules' => [
        [
            'entete' => [
                'taille' => 8,
                'modules' => [
                    [
                        'module' => 'formulaire_edition_element',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
            'entete_droite' => [
                'taille' => 4,
                'modules' => [

                    'graph_reponses' => [
                        'module' => 'graph_reponses',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
        ],
        'questions' => [
            'module' => 'questions',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        'reponses' => [
            'module' => 'reponses',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
    ]
];