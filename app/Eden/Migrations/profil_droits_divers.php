<?php

return [
    'table_libre' => [
        'nom_table' => "Profil droit divers",
        'nom_table_sql' => "profil_droits_divers",
        'description' => "",
        'element' => "profil droits divers",
        'type_element' => "profil_droits_divers",
        'element_pluriel' => "profil droits divers",
        'fiche' => 0,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'table_systeme' => 1
    ],
    'champs_libres' => [
        'type' => [
            'nom' => 'Type',
        ],
        'index' => [
            'nom' => 'Index'
        ],
        'profils' => [
            'nom' => 'Profils',
            'type' => 10,
            'type_element_ajax' => 'profil'
        ],
    ],
];