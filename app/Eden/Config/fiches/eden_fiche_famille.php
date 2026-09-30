<?php


return [
    'modules' =>
        [
            [
                [
                    'taille' => 8,
                    'onglets' => [],
                    'modules' => [

                        [
                            'module' => 'formulaire_edition_element',
                            'cacher_bloc_v_if' => '',
                            'afficher_par_defaut' => true,
                            'taille_avant' => 0,
                            'taille' => 12,
                            'taille_apres' => 0,
                        ],
                    ],
                ],
                [
                    'taille' => 4,
                    'onglets' => [],
                    'modules' => [

                        [
                            'module' => 'sous_famille',
                            'cacher_bloc_v_if' => '',
                            'afficher_par_defaut' => true,
                            'taille_avant' => 0,
                            'taille' => 12,
                            'taille_apres' => 0,
                        ],
                    ],
                ],
            ],
            [
                'module' => 'articles',
                'afficher_par_defaut' => true,
                'cacher_bloc_v_if' => '',
                'taille_avant' => 0,
                'taille' => 12,
                'taille_apres' => 0,
            ],
            [
                'module' => 'fiche_famille_categorie_comptable_famille',
                'cacher_bloc_v_if' => '1',
                'afficher_par_defaut' => 1,
                'taille_avant' => 0,
                'taille' => 12,
                'taille_apres' => 0,
            ],
        ],
];