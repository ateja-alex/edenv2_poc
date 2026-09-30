<?php
return [
    'modules' => [
        [
            [
                'taille' => 8,
                'onglets' => 0,
                'modules' => [

                    [
                        'module' => 'formulaire_edition_element',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'fiche_article_stocks',
                        'cacher_bloc_v_if' => 'article.stockable == 1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
            [
                'taille' => 4,
                'onglets' => 0,
                'modules' => [

                    [
                        'module' => 'indicateur_stock_actuel',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'famille_article',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => true,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'article_composants',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'pack_articles',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
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
                    ],
                ],
            ],
        ],
        [
            [
                'taille' => 12,
                'onglets' => 1,
                'modules' => [

                    [
                        'module' => 'commerce',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'fiche_article_article_fournisseur',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'fiche_article_categorie_comptable_article',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'gestion_images',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                    [
                        'module' => 'pieces_jointes',
                        'cacher_bloc_v_if' => '1',
                        'afficher_par_defaut' => 1,
                        'taille_avant' => 0,
                        'taille' => 12,
                        'taille_apres' => 0,
                    ],
                ],
            ],
        ],
    ],
    'colonne_droite' => []
];
