<?php

return [
    'table_libre' => [
        'nom_table' => "Aide utilisateur",
        'nom_table_sql' => "aide_utilisateur",
        'description' => "",
        'feminin' => "e",
        'element' => "aide utilisateur",
        'type_element' => "aide_utilisateur",
        'element_pluriel' => "aides utilisateurs",
        'fiche' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'parametre' => 1,
    ],
    'champs_libres' => [
        'index_aide' => [
            'nom' => "Index aide",
        ],
        'utilisateur_id' => [
            'nom' => "Utilisateur",
            'type' => 42,
            'type_element_ajax' => 'utilisateur',
        ],
    ],
];