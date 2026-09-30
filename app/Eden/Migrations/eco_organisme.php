<?php

return [
    'table_libre' => [
        'nom_table' => "Eco-organisme",
        'nom_table_sql' => "eco_organisme",
        'description' => "",
        'feminin' => "",
        'element' => "eco-organisme",
        'type_element' => "eco_organisme",
        'element_pluriel' => "eco-organismes",
        'fiche' => 1,
        
        'disponible_recherche_rapide' => 0,
        'creation_rapide' => 0,
        'affichage_dans_liste' => '#nom#',
    ],
    'champs_libres' => [
        'nom' => [
            'nom' => "Nom",
            'obligatoire' => 1,
            'recherche' => 1
        ],
    ],
];