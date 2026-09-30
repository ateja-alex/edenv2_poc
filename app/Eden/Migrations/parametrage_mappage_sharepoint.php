<?php

return [
    'table_libre' => [
        'nom_table' => "Paramétrage mappage Sharepoint",
        'nom_table_sql' => "parametrage_mappage_sharepoint",
        'description' => "Permet de définir quels types d'éléments vont être synchronisés dans Sharepoint",
        'feminin' => "",
        'element' => "parametrage_mappage_sharepoint",
        'type_element' => "parametrage_mappage_sharepoint",
        'element_pluriel' => "parametrages_mappage_sharepoint",
        'fiche' => 1,

        'disponible_recherche_rapide' => 1,
        'creation_rapide' => 0,
        'table_systeme' => 1,
        'affichage_dans_liste' => '#type_element#, #nom_dossier#',
    ],
    'champs_libres' => [
        'type_element' => [
            'nom' => "Type de l'élément",
            'obligatoire' => 1,
            'recherche' => 1,
        ],
        'nom_dossier' => [
            'nom' => "Nom du dossier",
            'obligatoire' => 1,
            'recherche' => 1,
        ],
        'id_microsoft' => [
            'nom' => "Id Microsoft",
            'recherche' => 1,
        ],
    ],
];
