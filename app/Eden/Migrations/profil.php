<?php

return [
    'table_libre' => [
        'nom_table' => "Profil",
        'nom_table_sql' => "profil",
        'description' => "",
        'element' => "profil",
        'type_element' => "profil",
        'element_pluriel' => "profils",
        'fiche' => 1,
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '#nom#',
        'table_systeme' => 1
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
            'recherche' => 1
        ],
        'extranet' => [
            'nom' => "Extranet",
            'type' => 20,
            'liste_choix' => 14
        ],
    ],
];