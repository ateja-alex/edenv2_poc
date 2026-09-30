<?php

return [
    'modules' => [
        [
            'entete' => [
                'taille' => 12,
                'modules' => [
                    [
                        'module' => 'formulaire_edition_element',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'fiche_campagne_de_prospection_campagne_de_prospection_client',
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
                    'recapitulatif' => [
                        'module' => 'recapitulatif',
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