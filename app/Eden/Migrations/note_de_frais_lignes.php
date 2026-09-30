<?php

return [
    'table_libre'   => [
        'nom_table'                   => "Note de frais lignes",
        'nom_table_sql'               => "",
        'description'                 => "",
        'feminin'                     => "",
        'element'                     => "note de frais lignes",
        'type_element'                => "note_de_frais_lignes",
        'element_pluriel'             => "Notes de frais lignes",
        'fiche'                       => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide'             => 0,
    ],
    'champs_libres' => [
        'note_de_frais_id' => [
            'nom'               => "Note de frais",
            'type'              => 42,
            'type_element_ajax' => "note_de_frais",
        ],
        'article_id' => [
            'nom'               => "Article",
            'type'              => 42,
            'type_element_ajax' => "article_note_de_frais",
        ],
        'montant_ht' => [
            'nom'               => "HT",
            'type' => 3,
        ],
        'taux_tva' => [
            'nom' => "Taux de tva",
            'type' => 42,
            'type_element_ajax' => 'code_tva',
            'filtres' => [
                'code_tva' => array (
                array (
                    'operateur' => 0,
                    'exclu' => 0,
                    'blocs' => array (),
                    'filtres' => 
                    array (
                    array (
                        'type_element' => 'code_tva',
                        'champ_liaison' => NULL,
                        'valeurs' => array (1),
                        'nom_sql' => 'sens',
                        'operateur' => 0,
                    ),
                    ),
                ),
                ), 
            ],
        ],
        'montant_ttc' => [
            'nom' => "TTC",
            'type' => 3,
        ],
        'plafond' => [
            'nom' => "Plafond",
            'type' => 3,
        ],
        'montant_rembourse' => [
            'nom' => "Montant remboursé",
            'type' => 3,
        ],
    ],
];