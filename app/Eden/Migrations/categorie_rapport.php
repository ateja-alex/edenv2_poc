<?php

return [
    'table_libre' => [
        'nom_table' => "Catégories rapports",
        'nom_table_sql' => "categorie_rapport",
        'description' => "",
        'feminin' => "",
        'element' => "Catégorie rapport",
        'type_element' => "categorie_rapport",
        'element_pluriel' => "Catégories rapports",
        'fiche' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'parametre' => 0,
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => 'Nom',
            'obligatoire' => 1,
        ],
        'index' => [
            'nom' => 'Index',
            'obligatoire' => 1,
        ],
        'ordre' => [
            'nom' => 'Ordre',
            'type' => 2,
        ],
    ],
];