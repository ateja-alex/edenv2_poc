<?php

return [
    'table_libre' => [
        'nom_table' => "Recherche avancée bloc",
        'nom_table_sql' => "recherche_avancee_bloc",
        'description' => "",
        'feminin' => "",
        'element' => "recherche avancee bloc",
        'type_element' => "recherche_avancee_bloc",
        'element_pluriel' => "recherches avancees blocs",
        'fiche' => 0,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'table_systeme' => 1,
    ],
    'champs_libres' => [
        'recherche_avancee_id' => [
            'nom' => "Recherche avancée",
            'type' => 42,
            'type_element_ajax' => 'recherche_avancee'
        ],
        'recherche_avancee_bloc_id' => [
            'nom' => "Bloc parent",
            'type' => 42,
            'type_element_ajax' => 'recherche_avancee_bloc'
        ],
        'operateur' => [
            'nom' => "Opérateur",
            'type' => 20,
            'liste_choix' => 650
        ],
        'exclu' => [
            'nom' => "Exclu",
            'type' => 20,
            'liste_choix' => 14
        ]
    ],
];