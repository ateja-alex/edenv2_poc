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
                    'interet_article' => [
                        'module' => 'interet_article',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
        ],
        [
            [
                'taille' => 8,
                'modules' => [

                    [
                        'module' => 'timeline',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
            [
                'taille' => 4,
                'modules' => [
                    [
                        'module' => 'commentaires',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
        ],
        [
            'module' => 'fiche_lead_proposition_commerciale',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
    ]
];