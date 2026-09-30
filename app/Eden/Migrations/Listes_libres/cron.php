<?php

return [

    'desactiver_actions' => 0,
    'desactiver_options' => 0,
    'desactiver_export' => 1,
    'desactiver_creation' => 1,
    'colonnes' => [

        array('nom' => '#', 'valeur' => 'id', 'ordre' => 0),
        array('nom' => 'Nom', 'valeur' => 'nom', 'ordre' => 1),
        array('nom' => 'En cours d\'exécution', 'type' => 'champ', 'champ' => 'en_cours', 'ordre' => 2),
        array('nom' => 'Date de dernière exécution', 'valeur' => 'derniere_execution', 'ordre' => 3),
        array('nom' => 'Temps avant relance (en heures)', 'type' => 'champ', 'champ' => 'temps_avant_relance', 'ordre' => 4),
        array('nom' => 'Standard', 'valeur' => 'standard', 'ordre' => 5),
    ],
    'calculs' => [],
    'filtres' => [],
    'couleurs' => [
        array(
            "couleur" => "#add5ff",
            "filtres" => [
              [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => [
                    [
                        'type_element' => 'cron',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => ["1"],
                        'nom_sql' => 'en_cours',
                    ],
                ]
              ],
            ],
        ),
        array(
            "couleur" => "#ffffff",
            "filtres" => [
              [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => [
                    [
                        'type_element' => 'cron',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => [
                            'debut' => null,
                            'fin' => null,
                            'variable' => "aujourdhui",
                        ],
                        'nom_sql' => 'derniere_execution',
                    ],
                ]
              ],
              [
                'operateur' => 1,
                'blocs' => [],
                'filtres' => [
                    [
                        'type_element' => 'cron',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => [
                            'debut' => null,
                            'fin' => null,
                            'variable' => "7_derniers_jours",
                        ],
                        'nom_sql' => 'derniere_execution',
                    ],
                ]
              ],
            ],
        ),
        array(
            "couleur" => "#c5c5c5",
            "filtres" => [
              [
                'operateur' => 0,
                'blocs' => [],
                'filtres' => [
                    [
                        'type_element' => 'cron',
                        'element_id' => null,
                        'champ_liaison' => null,
                        'valeurs' => ["0"],
                        'nom_sql' => 'en_cours',
                    ],
                ]
              ],
            ],
        ),
    ],
];