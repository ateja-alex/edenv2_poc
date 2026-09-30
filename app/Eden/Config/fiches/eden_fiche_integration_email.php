<?php

return [
    'modules' => [
        [
            'module' => 'formulaire_edition_element',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        [
            [
                'taille' => 12,
                'onglets' => 0,
                'modules' => [

                    [
                        'module' => 'fiche_integration_email_integration_email_comptes_emails',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
            [
                'taille' => 12,
                'onglets' => 0,
                'modules' => [

                    [
                        'module' => 'fiche_integration_email_integration_email_correspondance',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ]
        ],
    ]
];