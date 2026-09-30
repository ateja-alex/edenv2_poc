<?php

return [
    'table_libre' => [
        'nom_table' => "Conditionnements",
        'nom_table_sql' => "conditionnement",
        'description' => "",
        'feminin' => "",
        'element' => "conditionnement",
        'type_element' => "conditionnement",
        'element_pluriel' => "conditionnements",
        'fiche' => 0,
        'module' => 'Gestion commerciale',

        'disponible_recherche_rapide' => 1,
        'creation_rapide' => 0,
        'editable_client' => 1,
        'icone_fontawesome' => "fa-arrows-alt-h",
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
        ],
        'quantite' => [
            'nom' => "Quantité",
            'type' => 3,
            'obligatoire' => 1,
        ],
        'article_id' => [
            'nom' => "Article",
            'type' => 42,
            'type_element_ajax' => 'article',
            'obligatoire' => 1,
        ],
        'tarif' => [
            'nom' => "Tarif",
            'type' => 3,
        ],
        'prix_achat' => [
            'nom' => "Prix d'achat",
            'type' => 3,
        ],
    ],
];