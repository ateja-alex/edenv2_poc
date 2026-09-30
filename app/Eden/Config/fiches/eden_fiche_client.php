<?php


return [
    'modules' => [
        [
            'entete' => [
                'taille' => 8,
                'modules' => [
                    'graphique_ca_24_mois' => [
                        'module' => 'graphique_ca_24_mois',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],

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

                    'prospection' => [
                        'module' => 'prospection',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    'liste_contacts' => [
                        'module' => 'liste_contacts',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    'liste_adresses' => [
                        'module' => 'liste_adresses',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    'fiche_client_tache' => [
                        'module' => 'fiche_client_tache',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
        ],
        'liste_projets' => [
            'module' => 'liste_projets',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        'profil_familial_client' => [
            'module' => 'profil_familial_client',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        'commerce' => [
            'module' => 'commerce',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
        'fiche_client_paiement' => [
            'module' => 'fiche_client_paiement',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 6,
            'taille_apres' => 0,
        ],
        'recouvrement' => [
            'module' => 'recouvrement',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 6,
            'taille_apres' => 0,
        ],
        [
            'timeline_conteneur' => [
                'taille' => 8,
                'modules' => [

                    'timeline' => [
                        'module' => 'timeline',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
            'commentaires_conteneur' => [
                'taille' => 4,
                'modules' => [

                    'commentaires' => [
                        'module' => 'commentaires',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
        ],
        'pieces_jointes' => [
            'module' => 'pieces_jointes',
            'afficher_par_defaut' => true,
            'taille_avant' => 0,
            'taille' => 12,
            'taille_apres' => 0,
        ],
    ]
];