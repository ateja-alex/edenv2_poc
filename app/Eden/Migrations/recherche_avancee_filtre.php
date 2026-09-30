<?php

return [
    'table_libre' => [
        'nom_table' => "Recherche avancée filtre",
        'nom_table_sql' => "recherche_avancee_filtre",
        'description' => "",
        'feminin' => "",
        'element' => "recherche avancee filtre",
        'type_element' => "recherche_avancee_filtre",
        'element_pluriel' => "recherches avancees filtres",
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
        'type_element' => [
            'nom' => "Type élément"
        ],
        'nom_sql' => [
            'nom' => "Nom sql"
        ],
        'champ_liaison' => [
            'nom' =>  'Champ liaison'
        ],
        'valeurs' => [
            'nom' => 'Valeurs',
            'type' => 6
        ],
        'operateur' => [
            'nom' => "Opérateur",
            'type' => 20,
            'liste_choix' => 650
        ]
    ],
];