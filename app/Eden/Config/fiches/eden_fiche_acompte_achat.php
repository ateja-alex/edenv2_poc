<?php

return [
    'modules' => [
        [
            'module' => 'commentaire_fiche',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0
        ],
        [
            'module' => 'documents_lies',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        [
            [
                'taille' => 12,
                'onglets' => 1,
                'bouton_suivant' => 1,
                'modules' => [
                    [
                        'module' => 'fournisseur',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'informations_generales',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'saisie_des_articles',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'recap',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'fiche_acompte_achat_paiement',
                        'afficher_par_defaut' => 1,
                        'cacher_bloc_v_if' => '1',
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'historique',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ]
                ]
            ]
        ]
    ]
];
