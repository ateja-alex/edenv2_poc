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
                        'module' => 'fiche_export_compta_modele_export_compta_colonne',
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