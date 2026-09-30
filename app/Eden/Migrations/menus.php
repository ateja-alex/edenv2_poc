<?php

return [
    'table_libre' => [
        'nom_table' => "Menus",
        'nom_table_sql' => "menus",
        'description' => "",
        'feminin' => "",
        'element' => "menus",
        'type_element' => "menus",
        'element_pluriel' => "menus",
        'fiche' => 1,

        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom"
        ],
        'extranet' => [
            'nom' => "Extranet",
            'type' => 20,
            'liste_choix' => 14,
        ],
    ],
];