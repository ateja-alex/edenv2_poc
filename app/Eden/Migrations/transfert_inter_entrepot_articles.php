<?php

return [
    'table_libre' => [
        'nom_table' => "Pivot transfert inter entrepôt",
        'nom_table_sql' => "transfert_inter_entrepot_articles",
        'description' => "",
        'feminin' => "",
        'element' => "pivot transfert inter entrepot",
        'type_element' => "transfert_inter_entrepot_articles",
        'element_pluriel' => "pivots transferts inter entrepôts",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'parametre' => 0,
    ],
    'champs_libres' => [
        'article_id' => [
            'nom' => "Article",
            'type' => 42,
            'recherche' => 1,
            'type_element_ajax' => "article",
            'obligatoire' => 1,
        ],
        'transfert_inter_entrepot_id' => [
            'nom' => "Transfert inter entrepot",
            'type' => 42,
            'recherche' => 1,
            'type_element_ajax' => "transfert_inter_entrepot",
            'obligatoire' => 1,
        ],
        'quantite' => [
            'nom' => "Quantité",
            'type' => 3,
            'obligatoire' => 1,
        ],
        'conditionnement_id' => [
            'nom' => "Conditionnement",
            'type' => 42,
            'type_element_ajax' => 'conditionnement',
            'filtres' => [
                'conditionnement' => array (
                    array (
                        'operateur' => 0,
                        'exclu' => 0,
                        'blocs' => array (),
                        'filtres' => 
                        array (
                            array (
                                'type_element' => 'conditionnement',
                                'champ_liaison' => NULL,
                                'valeurs' => 'lien_champ|transfert_inter_entrepot_articles.article_id',
                                'nom_sql' => 'article_id',
                                'operateur' => 0,
                            ),
                        ),
                    ),
                ), 
            ],
            'desactiver_creation_a_la_volee' => true
        ],
    ],
];