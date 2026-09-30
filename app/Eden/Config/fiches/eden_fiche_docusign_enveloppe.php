<?php

return [
    'modules' => [
        [
            'module' => 'formulaire_affichage_element',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        [
            [
                'taille' => 6,
                'onglets' => 0,
                'modules' => [

                    [
                        'module' => 'fiche_docusign_enveloppe_docusign_signataire',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
            [
                'taille' => 6,
                'onglets' => 0,
                'modules' => [

                    [
                        'module' => 'fiche_docusign_enveloppe_docusign_document',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
        ],
    ]
];