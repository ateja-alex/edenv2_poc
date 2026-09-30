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
        ],
        'gestion_champs_libres' => [
            'module' => 'gestion_champs_libres',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ]
    ]
];