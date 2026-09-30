<?php

return [
    'table_libre' => [
        'nom_table' => "Condition commerciale",
        'nom_table_sql' => "",
        'description' => "",
        'feminin' => "",
        'element' => "condition commerciale",
        'type_element' => "condition_commerciale",
        'element_pluriel' => "conditions commerciales",
        'fiche' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '',
    ],
    'champs_libres' => [
        'catalogue_tarif_id' => [
            'nom' => "Catalogue tarif",
            'type' => 42,
            'type_element_ajax' => 'catalogue_tarif',
        ],
        'client_id' => [
            'nom' => "Client",
            'type' => 42,
            'type_element_ajax' => 'client',
        ],
        'famille_id' => [
            'nom' => "Famille",
            'type' => 42,
            'type_element_ajax' => 'famille',
        ],
        'article_id' => [
            'nom' => "Article",
            'type' => 42,
            'type_element_ajax' => 'article',
        ],
        'article_fournisseur_id' => [
            'nom' => "Article Fournisseur",
            'type' => 42,
            'type_element_ajax' => 'article_fournisseur',
        ],
        'palier_quantite' => [
            'nom' => "Palier de quantité",
            'type' => 3
        ],
        'tarif' => [
            'nom' => "Tarif",
            'type' => 3,
        ],
        'remise'=> [
            'nom' => "Remise",
            'type' => 3
        ],
        'prix_achat' => [
            'nom' => 'Prix achat',
            'type' => 3,
        ],
        'conditionnement' => [
            'nom' => 'Conditionnement',
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
                                'valeurs' => 'lien_champ|condition_commerciale.article_id',
                                'nom_sql' => 'article_id',
                                'operateur' => 0,
                            ),
                        ),
                    ),
                ), 
            ],
        ],
    ],
];