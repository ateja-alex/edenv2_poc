<?php

return [
    'table_libre' => [
        'nom_table' => "Modele de calculateur",
        'nom_table_sql' => "modele_de_calculateur",
        'description' => "",
        'feminin' => "",
        'element' => "modele_de_calculateur",
        'type_element' => "modele_de_calculateur",
        'element_pluriel' => "modeles_de_calculateur",
        'fiche' => 0,
        'module' => 'Gestion commerciale',
        
        'disponible_recherche_rapide' => 1,
        'creation_rapide' => 0,
        'editable_client' => 1,
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'recherche' => 1,
            'obligatoire' => 1,
        ],
        'calculateur' => [
            'nom' => "Calculateur",
            'type' => 6,
        ],
    ],
];